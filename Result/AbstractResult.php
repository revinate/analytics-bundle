<?php

namespace Revinate\AnalyticsBundle\Result;

use Revinate\AnalyticsBundle\Aggregation\AllAggregation;
use Revinate\AnalyticsBundle\BaseAnalyticsInterface;
use Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface;
use Revinate\AnalyticsBundle\Metric\Metric;
use Revinate\AnalyticsBundle\Metric\ProcessedMetric;
use Revinate\AnalyticsBundle\Query\QueryBuilder;

abstract class AbstractResult implements ResultInterface {

    /** @var  array */
    protected $raw;

    /** @var array result output in default format without any formatting */
    protected $nestedRaw;

    /** @var array formatted result output in default format */
    protected $nested;

    /** @var  \Elastica\ResultSet */
    protected $elasticaResultSet;

    /** @var array Internal keys that hold special data */
    public static $internalKeys = array("_info");
    /** @var QueryBuilder  */
    protected $queryBuilder;
    /** @var BaseAnalyticsInterface */
    protected $analytics;

    /**
     * @param QueryBuilder $queryBuilder
     * @param \Elastica\ResultSet $elasticaResultSet
     */
    public function __construct(QueryBuilder $queryBuilder, \Elastica\ResultSet $elasticaResultSet) {
        $this->queryBuilder = $queryBuilder;
        $this->analytics = $this->queryBuilder->getAnalytics();
        $this->elasticaResultSet = $elasticaResultSet;
        $start = microtime(true);
        $this->raw = $elasticaResultSet->getAggregations();
        // Nested Data is required for building all other views
        $this->nested = $this->calculateProcessedMetrics($this->buildNestedResult($this->raw, array(), 1, true), true);
        if ($this->queryBuilder->isDebug()) {
            echo "Result calculation Time: " . round(microtime(true)-$start, 3) . " secs";
        }
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
     * @return array
     */
    public function getNestedRaw() {
        if ($this->nestedRaw) {
            return $this->nestedRaw;
        }
        $this->nestedRaw = $this->calculateProcessedMetrics($this->buildNestedResult($this->raw, array(), 1, false), false);
        return $this->nestedRaw;
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
     * @oaram boolean $shouldFormat
     * @throws \Exception
     * @return mixed
     */
    protected function buildNestedResult($data, $result = array(), $depth = 1, $shouldFormat = false) {
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
                $bucketKeys = array();
                foreach ($dimensionData['buckets'] as $bucketIndex => $subDimensionData) {
                    $key = $this->getBucketKey($subDimensionData, $bucketIndex);
                    $subDimensionData = $this->unsetKeys($subDimensionData);
                    $bucketKeys[] = $key;
                    if (!isset($result[$dimension][$key])) {
                        $result[$dimension][$key] = array();
                    }
                    $result[$dimension][$key] = $this->buildNestedResult($subDimensionData, $result[$dimension][$key], $depth + 1, $shouldFormat);
                }
                // Add _info
                if ($filterSource && $this->queryBuilder->isEnableInfo()) {
                    $filterSourceValues = $this->convertToMap($filterSource->mget(array_unique($bucketKeys)), $filterSource->getIdColumn());
                    foreach ($dimensionData['buckets'] as $bucketIndex => $subDimensionData) {
                        $key = $this->getBucketKey($subDimensionData, $bucketIndex);
                        $result[$dimension][$key]["_info"] = isset($filterSourceValues[$key]) ? $filterSourceValues[$key] : array();
                    }
                }
                if ($dimensionObject->isReturnEmpty()) { // Null fill missing keys
                    $result[$dimension] = $this->addMissingDimensions($filterSource, $result[$dimension]);
                }
            } else { // If a metric
                $metric = $this->analytics->getMetric($dimension);
                $dimensionData = $this->unsetKeys($dimensionData);
                if ($metric && array_key_exists($metric->getResultKey(), $dimensionData)) {
                    $metricValue = $metric->getValue($dimensionData, $shouldFormat);
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
            if ($this->queryBuilder->isEnableInfo()) {
                $data[$row['id']] = array("_info" => $row);
            } else {
                $data[$row['id']] = array();
            }
        }
        return $data;
    }

    /**
     * Calculates Post Processed Metrics
     * @param array $result Results
     * @param boolean $shouldFormat Should apply formating to metrics
     * @return array
     */
    protected function calculateProcessedMetrics($result, $shouldFormat) {
        foreach ($result as $key => $values) {
            if (in_array($key, self::$internalKeys)) {
                continue;
            } else if (self::isArrayOfArray($values)) {
                $result[$key] = $this->calculateProcessedMetrics($values, $shouldFormat);
            } else if (! empty($values)) {
                $processedMetricNames = $this->getProcessedMetricNames($this->queryBuilder->getMetrics());
                $requestedMetricNames = $this->queryBuilder->getMetrics();
                $requestedProcessMetrics = array_intersect($processedMetricNames, $requestedMetricNames);
                foreach ($this->getOrderedListOfProcessedMetrics($requestedProcessMetrics) as $metricName) {
                    /** @var ProcessedMetric $metric */
                    $metric = $this->analytics->getMetric($metricName);
                    // Note: $result doesn't have raw value but formatted value which can have prefix and postfixes.
                    $metricValue = $metric ? call_user_func_array($metric->getPostProcessCallback(), $this->pickKeyValues($result[$key], $metric->getCalculatedFromMetrics())) : null;
                    if ($shouldFormat) {
                        $result[$key][$metric->getName()] = !is_null($metricValue) ? sprintf("%s%." . $metric->getPrecision() . "f%s", $metric->getPrefix(), $metricValue, $metric->getPostfix()) : null;
                    } else {
                        $result[$key][$metric->getName()] = !is_null($metricValue) ? $metricValue : null;
                    }
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
            if (! $metric) { continue; }
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
    public static function isArrayOfArray($array) {
        if (!is_array($array)) {
            return false;
        }
        foreach ($array as $key => $value) {
            if (self::isInternalKey($key)) {
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
     * @return bool
     */
    public static function isArrayOfArrayOfScalars($array) {
        if (! is_array($array)) {
            return false;
        }
        foreach ($array as $value) {
            if (! is_array($value)) {
                return false;
            }
            foreach ($value as $key => $scalar) {
                if (! self::isInternalKey($key) && (! is_scalar($scalar) && $scalar != null)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param $key
     * @return bool
     */
    public static function isInternalKey($key) {
        return in_array($key, self::$internalKeys);
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
        return array_values($pickedKeyValues);
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

    /**
     * @param array $metricNames array of metric names
     * @return array
     */
    protected function getProcessedMetricNames($metricNames) {
        $names = array();
        foreach ($metricNames as $name) {
            $metric = $this->analytics->getMetric($name);
            if ($metric && $metric instanceof ProcessedMetric) {
                $names[] = $metric->getName();
            }
        }
        return $names;
    }

    /**
     * @param array $data
     * @param $newKey
     * @return array
     */
    protected function convertToMap($data, $newKey) {
        $rekeyedData = array();
        foreach ($data as $value) {
            if (isset($value[$newKey])) {
                $rekeyedData[$value[$newKey]] = $value;
            }
        }
        return $rekeyedData;
    }
}