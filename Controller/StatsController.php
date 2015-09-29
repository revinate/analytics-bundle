<?php

namespace Revinate\AnalyticsBundle\Controller;

use Revinate\AnalyticsBundle\AnalyticsInterface;
use Revinate\AnalyticsBundle\Exception\InvalidResultFormatTypeException;
use Revinate\AnalyticsBundle\Filter\AnalyticsCustomFiltersInterface;
use Revinate\AnalyticsBundle\Metric\Result;
use Revinate\AnalyticsBundle\Query\BulkQueryBuilder;
use Revinate\AnalyticsBundle\Query\QueryBuilder;
use Revinate\AnalyticsBundle\Result\ResultSet;
use Revinate\AnalyticsBundle\Service\ElasticaService;
use Revinate\AnalyticsBundle\Test\Elastica\FilterHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;

class StatsController extends Controller {

    /**
     * Stats Controller
     * @param $source
     * @return JsonResponse
     * @throws \Exception
     */
    public function searchStatsAction($source) {
        $container = $this->get('service_container');
        $config = $container->getParameter('revinate_analytics.config');
        $post = json_decode($this->get('request_stack')->getMasterRequest()->getContent(), true);
        $format = isset($post['format']) ? $post['format'] : ResultSet::TYPE_NESTED;
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        if (empty($post) || empty($post['dimensions']) || empty($post['metrics'])) {
            return new JsonResponse(array('ok' => false, '_help' => $this->getHelp()), Response::HTTP_BAD_REQUEST);
        }

        $sourceConfig = $config['sources'][$source];
        /** @var AnalyticsInterface $analytics */
        $analytics = new $sourceConfig['class']($container);
        $isNestedDimensions = isset($post['flags']['nestedDimensions']) ? $post['flags']['nestedDimensions'] : false;
        /** @var ElasticaService $elasticaService */
        $elasticaService = $container->get('revinate_analytics.elastica');
        $queryBuilder = new QueryBuilder($elasticaService->getInstance(), $analytics);
        $queryBuilder
            ->setIsNestedDimensions($isNestedDimensions)
            ->addDimensions($post['dimensions'])
            ->addMetrics($post['metrics'])
            ->setGoals(isset($post['goals']) ? $post['goals'] : null)
        ;
        if (! empty($post['filters'])) {
            $queryBuilder->setFilter($this->getFilters($analytics, $post['filters']));
        }

        $response = array();
        $status = Response::HTTP_OK;
        try {
            $response = $queryBuilder->execute()->getResult($format);
        } catch (InvalidResultFormatTypeException $e) {
            $response = array('ok' => false, '_help' => $this->getHelp());
            $status = Response::HTTP_BAD_REQUEST;
        } catch (\Exception $e) {
            error_log(__METHOD__. " : Error getting analytics stats: " . $e->getMessage());
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }
        return new JsonResponse($response, $status);
    }

    /**
     * Bulk Stats Controller
     * @param $source
     * @return JsonResponse
     * @throws \Exception
     */
    public function bulkSearchStatsAction($source)
    {
        $container = $this->get('service_container');
        $config = $container->getParameter('revinate_analytics.config');
        $queriesPost = json_decode($this->get('request_stack')->getMasterRequest()->getContent(), true);
        $format = isset($queriesPost['format']) ? $queriesPost['format'] : ResultSet::TYPE_NESTED;
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        if (empty($queriesPost)) {
            return new JsonResponse(array('ok' => false, '_help' => $this->getHelp()), Response::HTTP_BAD_REQUEST);
        }

        $sourceConfig = $config['sources'][$source];
        /** @var AnalyticsInterface $analytics */
        $analytics = new $sourceConfig['class']($container);
        $bulkQueryBuilder = new BulkQueryBuilder();
        $isNestedDimensions = isset($queriesPost['flags']['nestedDimensions']) ? $queriesPost['flags']['nestedDimensions'] : false;
        foreach ($queriesPost['queries'] as $post) {
            if (empty($post) || empty($post['dimensions']) || empty($post['metrics'])) {
                return new JsonResponse(array('ok' => false, '_help' => $this->getHelp()), Response::HTTP_BAD_REQUEST);
            }
            /** @var ElasticaService $elasticaService */
            $elasticaService = $container->get('revinate_analytics.elastica');
            $queryBuilder = new QueryBuilder($elasticaService->getInstance(), $analytics);
            $queryBuilder
                ->setIsNestedDimensions($isNestedDimensions)
                ->addDimensions($post['dimensions'])
                ->addMetrics($post['metrics'])
                ->setGoals(isset($post['goals']) ? $post['goals'] : null);
            if (!empty($post['filters'])) {
                $queryBuilder->setFilter($this->getFilters($analytics, $post['filters']));
            }
            $bulkQueryBuilder->addQueryBuilder($queryBuilder);
        }

        $response = array('results' => array());
        $status = Response::HTTP_OK;
        try {
            $resultSets = $bulkQueryBuilder->execute();
            foreach ($resultSets as $resultSet) {
                $response['results'][] = $resultSet->getResult($format);
            }
            if (isset($queriesPost['comparator'])) {
                $response['comparator'] = $bulkQueryBuilder->getComparatorSet($queriesPost['comparator'])->get($format);
            }
        } catch (InvalidResultFormatTypeException $e) {
            $response = array('ok' => false, '_help' => $this->getHelp());
            $status = Response::HTTP_BAD_REQUEST;
        } catch (\Exception $e) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }
        return new JsonResponse($response, $status);
    }


    /**
     * @param \Revinate\AnalyticsBundle\AnalyticsInterface|AnalyticsCustomFiltersInterface $analytics
     * @param $postFilters
     * @throws \Exception
     * @return \Elastica\Filter\BoolAnd
     */
    protected function getFilters(AnalyticsInterface $analytics, $postFilters) {
        $andFilter = new \Elastica\Filter\BoolAnd();
        foreach ($postFilters as $name => $postFilter) {
            $type = $postFilter[0];
            $value = $postFilter[1];
            $filter = null;
            switch ($type) {
                case FilterHelper::TYPE_VALUE:
                    $filter = FilterHelper::getValueFilter($name, $value);
                    break;
                case FilterHelper::TYPE_RANGE:
                    $filter = FilterHelper::getRangeFilter($name, $value);
                    break;
                case FilterHelper::TYPE_EXISTS:
                    $filter = FilterHelper::getExistsFilter($name);
                    break;
                case FilterHelper::TYPE_MISSING:
                    $filter = FilterHelper::getMissingFilter($name);
                    break;
                case FilterHelper::TYPE_CUSTOM:
                    if (! $analytics instanceof AnalyticsCustomFiltersInterface) {
                        throw new \Exception(__METHOD__  . " Given analytics source does not implement AnalyticsCustomFiltersInterface");
                    }
                    $filter = $analytics->getCustomFilter($name)->getFilter($value);
                    break;
                default:
                    throw new \Exception(__METHOD__  . "Invalid filter passed");
            }
            $andFilter->addFilter($filter);
        }
        return $andFilter;
    }

    /**
     * @param $format
     * @param ResultSet $resultSet
     * @return array
     */
    protected function getFormattedResult($format, ResultSet $resultSet) {
        $isArray = is_array($format);
        $formats = $isArray ? $format : array($format);
        $results = array();
        foreach ($formats as $format) {
            $results[$format] = $resultSet->getResult($format);
        }
        return $isArray ? $results : $results[$format];
    }

    /**
     * @return array
     */
    protected function getHelp() {
        return array(
            'post' => array(
                'dimensions' => 'array of dimension names',
                'metrics' => 'array of metric names',
                'filters' => array(
                    'value' => array('field' => 'array values'),
                    'range' => array('field' => array('from' => 'value', 'to' => 'value'))
                ),
                'flags' => array(
                    'nestedDimensions' => 'true/false',
                ),
                'format' => 'flattened/raw/nested/tabular/google_data_table/chartjs'
            )
        );
    }
}