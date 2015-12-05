<?php

namespace Revinate\AnalyticsBundle\Controller;

use Revinate\AnalyticsBundle\Analytics;
use Revinate\AnalyticsBundle\AnalyticsInterface;
use Revinate\AnalyticsBundle\DimensionAggregate\DimensionAggregateSet;
use Revinate\AnalyticsBundle\Exception\InvalidResultFormatTypeException;
use Revinate\AnalyticsBundle\Filter\AnalyticsCustomFiltersInterface;
use Revinate\AnalyticsBundle\Goal\Goal;
use Revinate\AnalyticsBundle\Metric\Result;
use Revinate\AnalyticsBundle\Query\BulkQueryBuilder;
use Revinate\AnalyticsBundle\Query\QueryBuilder;
use Revinate\AnalyticsBundle\Result\ResultSet;
use Revinate\AnalyticsBundle\Service\ElasticaService;
use Revinate\AnalyticsBundle\Filter\FilterHelper;
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
        $dimensionAggregate = isset($post["dimensionAggregate"]) ? $post["dimensionAggregate"] : null;
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        if (empty($post) || empty($post['dimensions']) || empty($post['metrics'])) {
            return new JsonResponse(array('ok' => false, '_help' => $this->getHelp()), Response::HTTP_BAD_REQUEST);
        }
        $response = array("results" => array());
        $status = Response::HTTP_OK;
        try {
            $sourceConfig = $config['sources'][$source];
            /** @var Analytics $analytics */
            $analytics = new $sourceConfig['class']($container);
            $analytics->setContext(isset($post['context']) ? $post['context'] : array());
            $isNestedDimensions = isset($post['flags']['nestedDimensions']) ? $post['flags']['nestedDimensions'] : false;
            $isNestedDimensions = (is_bool($isNestedDimensions) && $isNestedDimensions) || $isNestedDimensions == "true";
            /** @var ElasticaService $elasticaService */
            $elasticaService = $container->get('revinate_analytics.elastica');
            $queryBuilder = new QueryBuilder($elasticaService->getInstance($source), $analytics);
            $queryBuilder
                ->setIsNestedDimensions($isNestedDimensions)
                ->addDimensions($post['dimensions'])
                ->addMetrics($post['metrics'])
            ;

            if (isset($post['goals'])) {
                $goals = array();
                foreach ($post["goals"] as $key => $val) {
                    $goals[] = new Goal($key, $val);
                }
                $queryBuilder->setGoals($goals);
            }

            if (! empty($post['filters'])) {
                $queryBuilder->setFilter($this->getFilters($analytics, $post['filters']));
            }

            $response['results'] = $queryBuilder->execute()->getResult($format);
            if (isset($post['goals'])) {
                $response['goalResults'] = $queryBuilder->getGoalSet()->get($format);
            }
            if ($dimensionAggregate) {
                $response["dimensionAggregate"] = $queryBuilder->getDimensionAggregateSet()->get($dimensionAggregate);
            }
        } catch (InvalidResultFormatTypeException $e) {
            $response = array('ok' => false, '_help' => $this->getHelp());
            $status = Response::HTTP_BAD_REQUEST;
        } catch (\Exception $e) {
            error_log(__METHOD__. " : Error getting analytics stats: " . $e->getMessage());
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response = array("ok" => false, "_help" => $e->getMessage());
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
        $dimensionAggregate = isset($queriesPost["dimensionAggregate"]) ? $queriesPost["dimensionAggregate"] : null;
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        if (empty($queriesPost)) {
            return new JsonResponse(array('ok' => false, '_help' => self::getHelp()), Response::HTTP_BAD_REQUEST);
        }

        $response = array('results' => array());
        $status = Response::HTTP_OK;
        try {
            $sourceConfig = $config['sources'][$source];
            /** @var Analytics $analytics */
            $analytics = new $sourceConfig['class']($container);
            $analytics->setContext(isset($post['context']) ? $post['context'] : array());
            $bulkQueryBuilder = new BulkQueryBuilder();
            $isNestedDimensions = isset($queriesPost['flags']['nestedDimensions']) ? $queriesPost['flags']['nestedDimensions'] == true : false;
            $isNestedDimensions = (is_bool($isNestedDimensions) && $isNestedDimensions) || $isNestedDimensions == "true";
            foreach ($queriesPost['queries'] as $post) {
                if (empty($post) || empty($post['dimensions']) || empty($post['metrics'])) {
                    return new JsonResponse(array('ok' => false, '_help' => $this->getHelp()), Response::HTTP_BAD_REQUEST);
                }
                /** @var ElasticaService $elasticaService */
                $elasticaService = $container->get('revinate_analytics.elastica');
                $queryBuilder = new QueryBuilder($elasticaService->getInstance($source), $analytics);
                $queryBuilder
                    ->setIsNestedDimensions($isNestedDimensions)
                    ->addDimensions($post['dimensions'])
                    ->addMetrics($post['metrics'])
                ;
                if (isset($queriesPost['goals'])) {
                    $goals = array();
                    foreach ($queriesPost["goals"] as $key => $val) {
                        $goals[] = new Goal($key, $val);
                    }
                    $queryBuilder->setGoals($goals);
                }
                if (!empty($post['filters'])) {
                    $queryBuilder->setFilter(self::getFilters($analytics, $post['filters']));
                }
                $bulkQueryBuilder->addQueryBuilder($queryBuilder);
            }

            $resultSets = $bulkQueryBuilder->execute();
            foreach ($resultSets as $resultSet) {
                $response['results'][] = $resultSet->getResult($format);
            }
            if ($bulkQueryBuilder->getGoalSets()) {
                $response['goalResults'] = array();
                foreach ($bulkQueryBuilder->getGoalSets() as $set) {
                    $response['goalResults'][] = $set->get($format);
                }
            }
            if ($dimensionAggregate) {
                $response["dimensionAggregate"] = array();
                foreach ($bulkQueryBuilder->getDimensionAggregateSets() as $set) {
                    $response["dimensionAggregate"][] = $set->get($dimensionAggregate);
                }
            }
            if (isset($queriesPost['comparator'])) {
                $response['comparator'] = $bulkQueryBuilder->getComparatorSet($queriesPost['comparator'])->get($format);
            }
        } catch (InvalidResultFormatTypeException $e) {
            $response = array('ok' => false, '_help' => $this->getHelp());
            $status = Response::HTTP_BAD_REQUEST;
        } catch (\Exception $e) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response = array("ok" => false, "_help" => $e->getMessage());
        }
        return new JsonResponse($response, $status);
    }


    /**
     * @param \Revinate\AnalyticsBundle\AnalyticsInterface|AnalyticsCustomFiltersInterface $analytics
     * @param $postFilters
     * @throws \Exception
     * @return \Elastica\Filter\BoolAnd
     */
    public static function getFilters(AnalyticsInterface $analytics, $postFilters) {
        $andFilter = new \Elastica\Filter\BoolAnd();
        foreach ($postFilters as $name => $postFilter) {
            if (count($postFilter) < 2) {
                throw new \Exception(__METHOD__  . "Invalid filter passed");
            }
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
                case FilterHelper::TYPE_PERIOD:
                    $filter = FilterHelper::getPeriodFilter($name, $value);
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
    public static function getHelp() {
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