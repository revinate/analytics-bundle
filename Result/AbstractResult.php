<?php

namespace Revinate\AnalyticsBundle\Result;

use Revinate\AnalyticsBundle\Aggregation\AllAggregation;
use Revinate\AnalyticsBundle\Metric\ProcessedMetric;
use Revinate\AnalyticsBundle\Query\QueryBuilder;

abstract class AbstractResult implements ResultInterface {

    /** @var  array */
    protected $raw;

    /** @var array */
    protected $nested;

    /** @var  \Elastica\ResultSet */
    protected $elasticaResultSet;

    /**
     * @param QueryBuilder $queryBuilder
     * @param \Elastica\ResultSet $elasticaResultSet
     */
    public function __construct(QueryBuilder $queryBuilder, \Elastica\ResultSet $elasticaResultSet) {
        $this->queryBuilder = $queryBuilder;
        $this->analytics = $this->queryBuilder->getAnalytics();
        $this->elasticaResultSet = $elasticaResultSet;
        $this->raw = $elasticaResultSet->getAggregations();
        // Nested Data is required for building all other views
        $this->nested = $this->calculateProcessedMetrics($this->buildNestedResult($this->raw));
    }

    /**
     * @return \Revinate\AnalyticsBundle\Analytics|\Revinate\AnalyticsBundle\AnalyticsInterface
     */
    public function getAnalytics() {
        return $this->analytics;
    }

    /**
     * @return \Revinate\AnalyticsBundle\Query\QueryBuilder
     */
    public function getQueryBuilder() {
        return $this->queryBuilder;
    }

    /**
     * @return array
     */
    public function getNested() {
        return $this->nested;
    }

    /**
     * @return array
     */
    public function getRaw() {
        return $this->raw;
    }

    /**
     * @return \Elastica\ResultSet
     */
    public function getElasticaResultSet() {
        return $this->elasticaResultSet;
    }

    /**
     * @param $data
     * @param $result
     * @param int $depth
     * @throws \Exception
     * @return mixed
     */
    protected function buildNestedResult($data, $result = array(), $depth = 1) {
        $isTopLevel = $depth == 1;
        foreach ($data as $dimension => $dimensionData) {
            // If Nested
            if ($this->isNested($dimension)) {
                $dimension = str_replace('__Nested', '', $dimension);
                // If Filter inside Nested
                $dimension = isset($dimensionData[$dimension."__Filter"]) ? $dimension."__Filter" : $dimension;
                $dimensionData = $dimensionData[$dimension];
            }

            // If filter
            if ($this->isFilter($dimension)) {
                $dimension = str_replace('__Filter', '', $dimension);
                $dimensionData = $dimensionData[$dimension];
            }

            if (isset($dimensionData['buckets'])) {
                foreach ($dimensionData['buckets'] as $subDimensionData) {
                    $key = isset($subDimensionData['key_as_string']) ? $subDimensionData['key_as_string']: $subDimensionData['key'];
                    $subDimensionData = $this->unsetKeys($subDimensionData);
                    if (!isset($result[$dimension][$key])) {
                        $result[$dimension][$key] = array();
                    }
                    $result[$dimension][$key] = $this->buildNestedResult($subDimensionData, $result[$dimension][$key], $depth + 1);
                }
            } else {
                $metric = $this->analytics->getMetric($dimension);
                $dimensionData = $this->unsetKeys($dimensionData);
                if (array_key_exists($metric->getResultKey(), $dimensionData)) {
                    $metricValue = $metric->getValue($dimensionData);
                    if ($isTopLevel) {
                        // If metric at top level, it must be for All Dimension
                        $result[AllAggregation::NAME][$dimension] = $metricValue;
                    } else {
                        $result[$dimension] = $metricValue;
                    }
                } else {
                    throw new \Exception("Invalid result " . $metric->getResultKey() . " specified for metric " . $dimension);
                }
            }
        }
        return $result;
    }

    /**
     * Calculates Post Processed Metrics
     * @param $result
     */
    protected function calculateProcessedMetrics($result) {
        foreach ($result as $key => $values) {
            if ($this->isArrayOfArray($values)) {
                $result[$key] = $this->calculateProcessedMetrics($values);
            } else {
                $processedMetricNames = $this->analytics->getProcessedMetricNames();
                $requestedMetricNames = $this->queryBuilder->getMetrics();
                $requestedProcessMetrics = array_intersect($processedMetricNames, $requestedMetricNames);
                foreach ($requestedProcessMetrics as $metricName) {
                    /** @var ProcessedMetric $metric */
                    $metric = $this->analytics->getMetric($metricName);
                    $metricValue = call_user_func_array($metric->getPostProcessCallback(), $this->pickKeyValues($values, $metric->getCalculatedFromMetrics()));
                    $result[$key][$metric->getName()] = round($metricValue, $metric->getPrecision());
                }
            }
        }
        return $result;
    }


    /**
     * @param array $array
     * @return bool
     */
    protected function isArrayOfArray($array) {
        if (!is_array($array)) {
            return false;
        }
        $first = array_shift($array);
        return is_array($first);
    }

    /**
     * @param $array
     * @return mixed
     */
    protected function unsetKeys($array) {
        unset($array['key']);
        unset($array['key_as_string']);
        unset($array['doc_count']);
        unset($array['to']);
        unset($array['from']);
        unset($array['to_as_string']);
        unset($array['from_as_string']);
        return $array;
    }

    /**
     * @param $array
     * @param string $joinBy
     * @return string
     */
    protected function getJoinedKey($array, $joinBy = '.') {
        return implode($joinBy, array_filter($array, function($v) { return strlen($v) > 0;}));
    }

    /**
     * @param $string
     * @return bool
     */
    protected function isFilter($string) {
        return strpos($string, '__Filter') !== false;
    }

    /**
     * @param $string
     * @return bool
     */
    protected function isNested($string) {
        return strpos($string, '__Nested') !== false;
    }

    /**
     * @param $data
     * @return bool
     */
    protected function isAllDimension($data) {
        return !isset($data['buckets']);
    }

    /**
     * @param $array
     * @param $keys
     * @return array
     */
    protected function pickKeyValues($array, $keys) {
        $pickedKeyValues = array();
        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                $pickedKeyValues[$key] = $array[$key];
            }
        }
        return $pickedKeyValues;
    }
}