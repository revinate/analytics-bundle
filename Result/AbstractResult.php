<?php

namespace Revinate\AnalyticsBundle\Result;

use Revinate\AnalyticsBundle\Aggregation\AllAggregation;
use Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface;
use Revinate\AnalyticsBundle\Metric\ProcessedMetric;
use Revinate\AnalyticsBundle\Query\QueryBuilder;

abstract class AbstractResult implements ResultInterface {

    /** @var  array */
    protected $raw;

    /** @var array */
    protected $nested;

    /** @var  \Elastica\ResultSet */
    protected $elasticaResultSet;

    /** @var array Internal keys that hold special data */
    protected static $internalKeys = array("_info");

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
            // If Nested or ReverseNested, go one level down
            if ($this->isNested($dimension) || $this->isReverseNested($dimension)) {
                $type = substr($dimension, strpos($dimension, "__"));
                $dimension = str_replace($type, '', $dimension);
                // Sometimes you have a filter, so look for it for presence
                $dimension = isset($dimensionData[$dimension."__Filter"]) ? $dimension."__Filter" : $dimension;
                $dimensionData = $dimensionData[$dimension];
            }

            // If filter
            if ($this->isFilter($dimension)) {
                $dimension = str_replace('__Filter', '', $dimension);
                $dimensionData = $dimensionData[$dimension];
            }

            // If a bucket
            if (isset($dimensionData['buckets'])) {
                $dimensionObject = $this->analytics->getDimension($dimension);
                $filterSource = $dimensionObject->getFilterSource();
                $result[$dimension] = array();
                foreach ($dimensionData['buckets'] as $bucketIndex => $subDimensionData) {
                    $key = $this->getBucketKey($subDimensionData, $bucketIndex);
                    $subDimensionData = $this->unsetKeys($subDimensionData);
                    if (!isset($result[$dimension][$key])) {
                        $result[$dimension][$key] = array();
                        if ($filterSource) {
                            $result[$dimension][$key]["_info"] = $filterSource->get($key);
                        }
                    }
                    $result[$dimension][$key] = $this->buildNestedResult($subDimensionData, $result[$dimension][$key], $depth + 1);
                }
                // Null fill missing keys
                $result[$dimension] = $this->addMissingDimensions($filterSource, $result[$dimension]);
            } else { // If a metric
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
     * @param FilterSourceInterface|null $filterSource
     * @param $data
     * @return mixed
     */
    protected function addMissingDimensions(FilterSourceInterface $filterSource = null, $data) {
        if (is_null($filterSource)) {
            return $data;
        }
        // Get all possible values for this filter source
        $all = $filterSource->getAll();
        foreach ($all as $row) {
            if (isset($data[$row['id']])) {
                continue;
            }
            $data[$row['id']] = array(
                "_info" => $row
            );
        }
        return $data;
    }

    /**
     * Calculates Post Processed Metrics
     * @param $result
     */
    protected function calculateProcessedMetrics($result) {
        foreach ($result as $key => $values) {
            if (in_array($key, self::$internalKeys)) {
                continue;
            } else if ($this->isArrayOfArray($values)) {
                $result[$key] = $this->calculateProcessedMetrics($values);
            } else {
                $processedMetricNames = $this->analytics->getProcessedMetricNames();
                $requestedMetricNames = $this->queryBuilder->getMetrics();
                $requestedProcessMetrics = array_intersect($processedMetricNames, $requestedMetricNames);
                foreach ($this->getOrderedListOfProcessedMetrics($requestedProcessMetrics) as $metricName) {
                    /** @var ProcessedMetric $metric */
                    $metric = $this->analytics->getMetric($metricName);
                    // Note: $result doesn't have raw value but formatted value which can have prefix and postfixes.
                    $metricValue = call_user_func_array($metric->getPostProcessCallback(), $this->pickKeyValues($result[$key], $metric->getCalculatedFromMetrics()));
                    $result[$key][$metric->getName()] = sprintf("%s%." . $metric->getPrecision() .  "f%s", $metric->getPrefix(), $metricValue, $metric->getPostfix());
                }
            }
        }
        return $result;
    }

    /**
     * @param ProcessedMetric[] $metricNames
     * @return string[]
     */
    protected function getOrderedListOfProcessedMetrics($metricNames) {
        $orderedMetrics = array();
        foreach ($metricNames as $metricName) {
            /** @var ProcessedMetric $metric */
            $metric = $this->analytics->getMetric($metricName);
            if ($metric->isDependentOnProcessedMetric($this->analytics)) {
                foreach ($this->getOrderedListOfProcessedMetrics($metric->getCalculatedFromProcessedMetricsOnly($this->analytics)) as $processedMetricName) {
                    $orderedMetrics[] = $processedMetricName;
                }
                $orderedMetrics[] = $metricName;
            } else {
                $orderedMetrics[] = $metricName;
            }
        }
        return $orderedMetrics;
    }

    /**
     * @param array $array
     * @return bool
     */
    protected function isArrayOfArray($array) {
        if (!is_array($array)) {
            return false;
        }
        foreach ($array as $key => $value) {
            if (in_array($key, self::$internalKeys)) {
                continue;
            }
            if (is_array($value)) {
                return true;
            }
        }
        return false;
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
     * @param $string
     * @return bool
     */
    protected function isReverseNested($string) {
        return strpos($string, '__ReverseNested') !== false;
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
            $pickedKeyValues[$key] = array_key_exists($key, $array) ? $array[$key] : null;
        }
        return $pickedKeyValues;
    }

    /**
     * @param array     $data
     * @param string    $bucketIndex
     * @return string
     */
    protected function getBucketKey($data, $bucketIndex) {
        if (isset($data['key_as_string']))  {
            return $data['key_as_string'];
        }
        if (isset($data['key'])) {
            return $data['key'];
        }
        return $bucketIndex;
    }
}