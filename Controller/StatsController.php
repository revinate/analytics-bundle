<?php

namespace Revinate\AnalyticsBundle\Controller;

use Revinate\AnalyticsBundle\BaseAnalyticsInterface;
use Revinate\AnalyticsBundle\Exception\InvalidResultFormatTypeException;
use Revinate\AnalyticsBundle\Goal\Goal;
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
        $dateRange = isset($post['dateRange']) ? $post['dateRange'] : null;
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
            /** @var BaseAnalyticsInterface $analytics */
            $analytics = new $sourceConfig['class']($container);
            $analytics->setContext(isset($post['context']) ? $post['context'] : array());
            $analytics->setDateRange($dateRange);
            $isNestedDimensions = $this->getBoolRequestFlag($post, 'nestedDimensions', false);
            $isInfoEnabled = $this->getBoolRequestFlag($post, 'enableInfo', true);
            /** @var ElasticaService $elasticaService */
            $elasticaService = $container->get('revinate_analytics.elastica');
            $queryBuilder = new QueryBuilder($elasticaService->getInstance($source), $analytics);
            $queryBuilder
                ->setIsNestedDimensions($isNestedDimensions)
                ->addDimensions($post['dimensions'])
                ->addMetrics($post['metrics'])
                ->setEnableInfo($isInfoEnabled)
            ;
            if (isset($post['goals'])) {
                $goals = array();
                foreach ($post["goals"] as $key => $val) {
                    $goals[] = new Goal($key, $val);
                }
                $queryBuilder->setGoals($goals);
            }
            $filters = isset($post['filters']) ? $post['filters'] : array();
            if(! is_null($dateRange)) {
                if (! isset($dateRange[2])) {
                    throw new InvalidResultFormatTypeException();
                }
                $dateFilterName = $dateRange[2];
                $filters[$dateFilterName] = array($dateRange[0], $dateRange[1], $dateRange[2]);
                $queryBuilder->setBounds($dateRange);
            }
            if (!empty($filters)) {
                $queryBuilder->setFilter(self::getFilters($analytics, $filters));
            }
            if (isset($post['sort'])) {
                $queryBuilder->setSort($post['sort']);
            }
            $response['results'] = $queryBuilder->execute()->getResult($format);
            if (isset($post['goals'])) {
                $response['goalResults'] = $queryBuilder->getGoalSet()->get($format);
            }
            if ($dimensionAggregate) {
                $response["dimensionAggregate"] = array();
                $type = isset($dimensionAggregate['type']) ? $dimensionAggregate['type'] : null;
                $info = isset($dimensionAggregate['info']) ? $dimensionAggregate['info'] : null;
                if (is_null($type)) {
                    throw new InvalidResultFormatTypeException("Invalid dimension aggregate: " . print_r($dimensionAggregate, 1));
                }
                $response["dimensionAggregate"] = $queryBuilder->getDimensionAggregateSet()->get($type, $info);
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
            /** @var BaseAnalyticsInterface $analytics */
            $analytics = new $sourceConfig['class']($container);
            $analytics->setContext(isset($post['context']) ? $post['context'] : array());
            $bulkQueryBuilder = new BulkQueryBuilder();
            $isNestedDimensions = $this->getBoolRequestFlag($queriesPost, 'nestedDimensions', false);
            $isInfoEnabled = $this->getBoolRequestFlag($queriesPost, 'enableInfo', true);
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
                    ->setEnableInfo($isInfoEnabled)
                ;
                if (isset($queriesPost['goals'])) {
                    $goals = array();
                    foreach ($queriesPost["goals"] as $key => $val) {
                        $goals[] = new Goal($key, $val);
                    }
                    $queryBuilder->setGoals($goals);
                }
                $filters = isset($post['filters']) ? $post['filters'] : array();
                $dateRange = isset($post['dateRange']) ? $post['dateRange'] : null;
                /**
                 * HACK: We are changing the analytics class date range for each of the queries.
                 * This is because some metrics in analytics class can depend on this date range. like "*_pace metrics"
                 * which depend on the period of the query.
                 */
                $analytics->setDateRange($dateRange);
                if (! is_null($dateRange)) {
                    if (! isset($dateRange[2])) {
                        throw new InvalidResultFormatTypeException();
                    }
                    $dateFilterName = $dateRange[2];
                    $filters[$dateFilterName] = array($dateRange[0], $dateRange[1], $dateRange[2]);
                    $queryBuilder->setBounds($dateRange);
                }
                if (!empty($filters)) {
                    $queryBuilder->setFilter(self::getFilters($analytics, $filters));
                }
                if (isset($post['sort'])) {
                    $queryBuilder->setSort($post['sort']);
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
                $type = isset($dimensionAggregate['type']) ? $dimensionAggregate['type'] : null;
                $info = isset($dimensionAggregate['info']) ? $dimensionAggregate['info'] : null;
                if (is_null($type)) {
                    throw new InvalidResultFormatTypeException("Invalid dimension aggregate: " . print_r($dimensionAggregate,1));
                }
                foreach ($bulkQueryBuilder->getDimensionAggregateSets() as $set) {
                    $response["dimensionAggregate"][] = $set->get($type, $info);
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
     * @param BaseAnalyticsInterface $analytics
     * @param $postFilters
     * @throws \Exception
     * @return \Elastica\Filter\BoolAnd
     */
    public static function getFilters(BaseAnalyticsInterface $analytics, $postFilters) {
        $andFilter = new \Elastica\Filter\BoolAnd();
        foreach ($postFilters as $name => $postFilter) {
            if (count($postFilter) < 2) {
                throw new \Exception(__METHOD__  . "Invalid filter passed");
            }
            $type = $postFilter[0];
            $value = $postFilter[1];
            if ($type == FilterHelper::TYPE_PERIOD && count($postFilter) < 3) {
                throw new \Exception(__METHOD__  . "Invalid filter passed");
            }
            $filter = null;
            switch ($type) {
                case FilterHelper::TYPE_VALUE:
                    $filter = FilterHelper::getValueFilter($name, $value);
                    break;
                case FilterHelper::TYPE_RANGE:
                    $filter = FilterHelper::getRangeFilter($name, $value);
                    break;
                case FilterHelper::TYPE_PERIOD:
                    $typeComparator = isset($postFilter[3]) ? $postFilter[3] : FilterHelper::TYPE_TIME_COMPARATOR_DATE;
                    $filter = FilterHelper::getPeriodFilter($postFilter[2], $value, $typeComparator);
                    break;
                case FilterHelper::TYPE_EXISTS:
                    $filter = FilterHelper::getExistsFilter($name);
                    break;
                case FilterHelper::TYPE_MISSING:
                    $filter = FilterHelper::getMissingFilter($name);
                    break;
                case FilterHelper::TYPE_CUSTOM:
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
                'format' => 'flattened/raw/nested/tabular/google_data_table/chartjs',
                'dateRange' => array('period/range', 'period name/from=>to', 'name of date field')
            )
        );
    }

    /**
     * @param array $post
     * @param string $key
     * @param mixed $default
     * @return bool
     */
    protected function getBoolRequestFlag($post, $key, $default) {
        $value = isset($post['flags'][$key]) ? $post['flags'][$key] : $default;
        $value = (is_bool($value) && $value) || $value == "true";
        return $value;
    }
}
