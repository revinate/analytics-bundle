<?php

namespace Revinate\AnalyticsBundle\Query;


use Elastica\Aggregation\AbstractAggregation;
use Revinate\AnalyticsBundle\Aggregation\AllAggregation;
use Revinate\AnalyticsBundle\Analytics;
use Revinate\AnalyticsBundle\AnalyticsInterface;
use Revinate\AnalyticsBundle\Dimension\AllDimension;
use Revinate\AnalyticsBundle\Dimension\DateHistogramDimension;
use Revinate\AnalyticsBundle\Dimension\DateRangeDimension;
use Revinate\AnalyticsBundle\Dimension\HistogramDimension;
use Revinate\AnalyticsBundle\Dimension\RangeDimension;
use Revinate\AnalyticsBundle\Metric\ProcessedMetric;
use Revinate\AnalyticsBundle\Metric\Result;
use Revinate\AnalyticsBundle\Result\ResultSet;

/**
 * Class QueryBuilder
 * @package Revinate\AnalyticsBundle\Query
 */
class QueryBuilder {
    /** @var  AnalyticsInterface|Analytics */
    protected $analytics;
    /** @var  \Elastica\Client  */
    protected $elasticaClient;
    /** @var  array */
    protected $dimensions = array();
    /** @var  array */
    protected $metrics = array();
    /** @var  \Elastica\Filter\AbstractFilter */
    protected $filter;
    /** @var  bool */
    protected $isNestedDimensions = false;
    /** @var int */
    protected $offset = 0;
    /** @var int */
    protected $size = 10;

    /**
     * @param \Elastica\Client $elasticaClient
     * @param AnalyticsInterface $analytics
     */
    public function __construct(\Elastica\Client $elasticaClient, AnalyticsInterface $analytics) {
        $this->elasticaClient = $elasticaClient;
        $this->analytics = $analytics;
        return $this;
    }

    /**
     * @param $dimension
     * @return $this
     */
    public function addDimension($dimension) {
        $this->dimensions[] = $dimension;
        return $this;
    }

    /**
     * @param string[] $dimensions
     * @return $this
     */
    public function addDimensions($dimensions) {
        $this->dimensions = $dimensions;
        return $this;
    }

    /**
     * @param $metric
     * @return $this
     */
    public function addMetric($metric) {
        $this->metrics[] = $metric;
        return $this;
    }

    /**
     * @param string[] $metrics
     * @return $this
     */
    public function addMetrics($metrics) {
        $this->metrics = $metrics;
        return $this;
    }

    /**
     * @return array
     */
    public function getDimensions() {
        return $this->dimensions;
    }

    /**
     * @return array
     */
    public function getMetrics() {
        return $this->metrics;
    }

    /**
     * @return \Revinate\AnalyticsBundle\Analytics|\Revinate\AnalyticsBundle\AnalyticsInterface
     */
    public function getAnalytics() {
        return $this->analytics;
    }

    /**
     * @param boolean $isNestedDimensions
     * @return $this
     */
    public function setIsNestedDimensions($isNestedDimensions) {
        $this->isNestedDimensions = $isNestedDimensions;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsNestedDimensions() {
        return $this->isNestedDimensions;
    }

    /**
     * @param \Elastica\Filter\AbstractFilter $filter
     * @return $this
     */
    public function setFilter($filter) {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @param int $size
     */
    public function setSize($size) {
        $this->size = $size;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset) {
        $this->offset = $offset;
    }

    /**
     * @return \Elastica\Aggregation\AbstractAggregation[]
     */
    protected function createDimensionAggregations() {
        $dimensionAggregations = array();
        foreach ($this->dimensions as $dimensionName) {
            $dimension = $this->analytics->getDimension($dimensionName);

            if ($dimension instanceof AllDimension) {
                $dimensionAgg = new AllAggregation($dimension->getName());

            } else if ($dimension instanceof DateHistogramDimension) {
                $dimensionAgg = new \Elastica\Aggregation\DateHistogram($dimension->getName(), $dimension->getField(), $dimension->getInterval());
                $dimensionAgg->setFormat($dimension->getFormat());
                $dimensionAgg->setMinimumDocumentCount(0);

            } else if ($dimension instanceof HistogramDimension) {
                $dimensionAgg = new \Elastica\Aggregation\Histogram($dimension->getName(), $dimension->getField(), $dimension->getInterval());
                $dimensionAgg->setMinimumDocumentCount(0);

            } else if ($dimension instanceof DateRangeDimension) {
                $dimensionAgg = new \Elastica\Aggregation\DateRange($dimension->getName());
                $dimensionAgg->setField($dimension->getField());
                $dimensionAgg->setFormat($dimension->getFormat());
                foreach ($dimension->getRanges() as $range) {
                    $dimensionAgg->addRange($range["from"], $range["to"]);
                }

            } else if ($dimension instanceof RangeDimension) {
                $dimensionAgg = new \Elastica\Aggregation\Range($dimension->getName());
                $dimensionAgg->setField($dimension->getField());
                foreach ($dimension->getRanges() as $range) {
                    $dimensionAgg->addRange($range["from"], $range["to"]);
                }

            } else { // $dimension instanceof Dimension
                $dimensionAgg = new \Elastica\Aggregation\Terms($dimension->getName());
                $dimensionAgg->setField($dimension->getField());
                $dimensionAgg->setSize($dimension->getSize());
                //$dimensionAgg->setOrder()
            }

            $dimensionAggregations[] = $dimensionAgg;
        }
        return $dimensionAggregations;
    }

    /**
     * @return \Elastica\Aggregation\AbstractAggregation[]
     */
    protected function createMetricAggregations() {
        $metricAggregations = array();
        foreach ($this->getAllMetricsRequiredForPostProcessing($this->metrics) as $metricName) {
            $metric = $this->analytics->getMetric($metricName);

            // Leaf level aggregation for the metric itself
            if (Result::COUNT == $metric->getResult()) {
                $metricAgg = new \Elastica\Aggregation\ValueCount($metric->getName(), $metric->getField());

            } else if (Result::SUM == $metric->getResult()) {
                $metricAgg = new \Elastica\Aggregation\Sum($metric->getName());
                $metricAgg->setField($metric->getField());

            } else if (Result::AVG == $metric->getResult()) {
                $metricAgg = new \Elastica\Aggregation\Avg($metric->getName());
                $metricAgg->setField($metric->getField());

            } else if (in_array($metric->getResult(), array(Result::MIN, Result::MAX))) {
                $metricAgg = new \Elastica\Aggregation\Stats($metric->getName());
                $metricAgg->setField($metric->getField());

            } else {
                $metricAgg = new \Elastica\Aggregation\ExtendedStats($metric->getName());
                $metricAgg->setField($metric->getField());
            }

            // Add Metric Filter if any
            if (!is_null($metric->getFilter())) {
                $metricFilterAgg = new \Elastica\Aggregation\Filter($metric->getName() . "__Filter");
                $metricFilterAgg->setFilter($metric->getFilter());
                $metricFilterAgg->addAggregation($metricAgg);
            } else {
                $metricFilterAgg = $metricAgg;
            }

            // Nested Metrics if any
            if (!is_null($metric->getNestedPath())) {
                $metricNestedAgg = new \Elastica\Aggregation\Nested($metric->getName() . "__Nested", $metric->getNestedPath());
                $metricNestedAgg->addAggregation($metricFilterAgg);
            } else {
                $metricNestedAgg = $metricFilterAgg;
            }

            $metricAggregations[] = $metricNestedAgg;
        }
        return $metricAggregations;
    }

    /**
     * @param $aggregations
     * @return AbstractAggregation[]
     */
    protected function removeAllAggregation($aggregations) {
        return array_values(array_filter($aggregations, function($aggregation) { return !($aggregation instanceof AllAggregation); }));
    }

    /**
     * Gets all metrics that are required to calculate given $metrics
     * @param $metrics
     * @return array
     */
    protected function getAllMetricsRequiredForPostProcessing($metrics) {
        $allMetrics = array();
        foreach ($metrics as $metricName) {
            $metric = $this->analytics->getMetric($metricName);
            if ($metric instanceof ProcessedMetric) {
                $allMetrics = array_merge($allMetrics, $metric->getCalculatedFromMetrics());
            } else {
                $allMetrics[] = $metricName;
            }
        }
        return array_unique($allMetrics);
    }

    /**
     * @return \Elastica\Query
     */
    public function getQuery() {
        $query = new \Elastica\Query();
        $query->setSize($this->size);
        $query->setFrom($this->offset);

        // Create Dimensions and Metric Aggregations
        $dimensionAggregations = $this->createDimensionAggregations();
        $metricAggregations = $this->createMetricAggregations();

        // Combine Aggregations
        if ($this->isNestedDimensions && count($dimensionAggregations) > 1) {
            // Nested Aggregations
            $dimensionAggregations = $this->removeAllAggregation($dimensionAggregations);
            $firstDimensionAggregation = $dimensionAggregations[0];
            $previousDimensionAggregation = $firstDimensionAggregation;
            /** @var \Elastica\Aggregation\AbstractAggregation $dimensionAggregation */
            foreach ($dimensionAggregations as $dimensionIndex => $dimensionAggregation) {
                if ($dimensionIndex == 0) { continue; }
                // If Last Dimension
                if ($dimensionIndex == count($dimensionAggregations) - 1) {
                    foreach ($metricAggregations as $metricAggregation) {
                        $dimensionAggregation->addAggregation($metricAggregation);
                    }
                }
                $previousDimensionAggregation->addAggregation($dimensionAggregation);
                $previousDimensionAggregation = $dimensionAggregation;
            }
            $query->addAggregation($firstDimensionAggregation);

        } else if (count($dimensionAggregations) >= 1) {
            // Top Level Aggregations
            foreach ($dimensionAggregations as $dimensionAggregation) {
                if ($dimensionAggregation instanceof AllAggregation) {
                    // All Aggregation is added to the top level
                    foreach ($metricAggregations as $metricAggregation) {
                        $query->addAggregation($metricAggregation);
                    }
                } else {
                    foreach ($metricAggregations as $metricAggregation) {
                        $dimensionAggregation->addAggregation($metricAggregation);
                    }
                    $query->addAggregation($dimensionAggregation);
                }
            }
        }

        // Add Filter
        if (!is_null($this->filter)) {
            $filteredQuery = new \Elastica\Query\Filtered();
            $filteredQuery->setFilter($this->filter);
            $query->setQuery($filteredQuery);
        }
        return $query;
    }

    /**
     * @return ResultSet
     */
    public function execute() {
        $query = $this->getQuery();

        $search = new \Elastica\Search($this->elasticaClient);
        $search->addIndex($this->analytics->getIndex());
        $search->addType($this->analytics->getType());
        $search->setQuery($query);

        $elasticaResultSet = $search->search();
        return new ResultSet($this, $elasticaResultSet);
    }
}