<?php

namespace Revinate\AnalyticsBundle\Query;


use Elastica\Aggregation\AbstractAggregation;
use Elastica\Filter\AbstractFilter;
use Revinate\AnalyticsBundle\Aggregation\AllAggregation;
use Revinate\AnalyticsBundle\Aggregation\Nested;
use Revinate\AnalyticsBundle\Analytics;
use Revinate\AnalyticsBundle\AnalyticsInterface;
use Revinate\AnalyticsBundle\DimensionAggregate\DimensionAggregateSet;
use Revinate\AnalyticsBundle\Dimension\AllDimension;
use Revinate\AnalyticsBundle\Dimension\DateHistogramDimension;
use Revinate\AnalyticsBundle\Dimension\DateRangeDimension;
use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\Dimension\FiltersDimension;
use Revinate\AnalyticsBundle\Dimension\HistogramDimension;
use Revinate\AnalyticsBundle\Dimension\RangeDimension;
use Revinate\AnalyticsBundle\Goal\Goal;
use Revinate\AnalyticsBundle\Goal\GoalSet;
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
    /** @var int Number of documents to return */
    protected $size = 0;
    /** @var array  */
    protected $sort;
    /** @var  Goal[] */
    protected $goals;
    /** @var  \Elastica\ResultSet */
    protected $elasticaResultSet;
    /** @var  ResultSet */
    protected $resultSet;
    /** @var  bool */
    protected $debug = false;

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
     * @param string $dimension
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
     * @param AbstractFilter $filter
     * @return $this
     */
    public function setFilter(AbstractFilter $filter) {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setSize($size) {
        $this->size = $size;
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function setOffset($offset) {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return AbstractFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return array
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param array $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return Goal[]
     */
    public function getGoals() {
        return $this->goals;
    }

    /**
     * @param Goal[] $goals
     * @return $this
     */
    public function setGoals($goals) {
        $this->goals = $goals;
        return $this;
    }

    /**
     * @return \Elastica\Aggregation\AbstractAggregation[]
     */
    protected function createDimensionAggregations() {
        $dimensionAggregations = array();
        foreach ($this->dimensions as $dimensionName) {
            $dimension = $this->analytics->getDimension($dimensionName);
            $dimensionAggregations[] = $this->getAggregationFromDimension($dimension);
        }
        return $dimensionAggregations;
    }

    /**
     * @param Dimension $dimension
     * @param bool $isNested
     * @return \Elastica\Aggregation\DateHistogram|\Elastica\Aggregation\DateRange|\Elastica\Aggregation\Histogram|\Elastica\Aggregation\Range|\Elastica\Aggregation\Terms|AllAggregation
     */
    protected function getAggregationFromDimension(Dimension $dimension, $isNested = false) {
        if ($dimension instanceof AllDimension) {
            $dimensionAgg = new AllAggregation($dimension->getName());

        } elseif ($dimension instanceof DateHistogramDimension) {
            $dimensionAgg = new \Elastica\Aggregation\DateHistogram($dimension->getName(), $dimension->getField(), $dimension->getInterval());
            $dimensionAgg->setFormat($dimension->getFormat());
            $dimensionAgg->setMinimumDocumentCount(0);

        } elseif ($dimension instanceof HistogramDimension) {
            $dimensionAgg = new \Elastica\Aggregation\Histogram($dimension->getName(), $dimension->getField(), $dimension->getInterval());
            $dimensionAgg->setMinimumDocumentCount(0);


        } elseif ($dimension instanceof DateRangeDimension) {
            $dimensionAgg = new \Elastica\Aggregation\DateRange($dimension->getName());
            $dimensionAgg->setField($dimension->getField());
            $dimensionAgg->setFormat($dimension->getFormat());
            foreach ($dimension->getRanges() as $range) {
                $dimensionAgg->addRange($range["from"], $range["to"]);
            }

        } elseif ($dimension instanceof RangeDimension) {
            $dimensionAgg = new \Elastica\Aggregation\Range($dimension->getName());
            $dimensionAgg->setField($dimension->getField());
            foreach ($dimension->getRanges() as $range) {
                $dimensionAgg->addRange($range["from"], $range["to"]);
            }

        } elseif ($dimension->getPath()) { // Nested Dimension
            // If any dimension is nested, wrap it in a "Nested Aggregation".
            $dimensionAgg = new Nested($dimension->getName() . '__Nested', $dimension->getPath());
            $subDimension = clone($dimension);
            $subDimension->setPath(null);
            $dimensionAgg->addSubAggregation($this->getAggregationFromDimension($subDimension, true));

        } elseif ($dimension instanceof FiltersDimension) {
            $dimensionAgg = new \Elastica\Aggregation\Filters($dimension->getName());
            foreach ($dimension->getFilters() as $name => $filter) {
                $dimensionAgg->addFilter($filter, $name);
            }

        } else { // $dimension instanceof Dimension
            $dimensionAgg = new \Elastica\Aggregation\Terms($dimension->getName());
            $dimensionAgg->setField($dimension->getField());
            $dimensionAgg->setSize($dimension->getSize());
            // Note: The path for nested metric is complex and not known to clients
            // Eg: Nested metric path can be "metric__ReverseNested.metric" rather than just being "metric"
            if ($this->getSort() && ! $isNested) {
                $dimensionAgg->setParam("order", $this->getSort());
            }
        }
        return $dimensionAgg;
    }

    /**
     * @return \Elastica\Aggregation\AbstractAggregation[]
     */
    protected function createMetricAggregations() {
        $metricAggregations = array();
        foreach ($this->getAllMetricsRequiredForPostProcessing($this->metrics) as $metricName) {
            $metric = $this->analytics->getMetric($metricName);

            // Leaf level aggregation for the metric itself
            if ($metric->isResultOfType(Result::COUNT)) {
                $metricAgg = new \Elastica\Aggregation\ValueCount($metric->getName(), $metric->getField());

            } elseif ($metric->isResultOfType(Result::SUM)) {
                $metricAgg = new \Elastica\Aggregation\Sum($metric->getName());
                $metricAgg->setField($metric->getField());

            } elseif ($metric->isResultOfType(Result::AVG)) {
                $metricAgg = new \Elastica\Aggregation\Avg($metric->getName());
                $metricAgg->setField($metric->getField());

            } elseif ($metric->isResultOfType(Result::MIN) || $metric->isResultOfType(Result::MAX)) {
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
                $metricNestedAgg = new Nested($metric->getName() . "__Nested", $metric->getNestedPath());
                $metricNestedAgg->addSubAggregation($metricFilterAgg);
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
                $calulatedFromMetrics = $this->getAllMetricsRequiredForPostProcessing($metric->getCalculatedFromMetrics());
                $allMetrics = array_merge($allMetrics, $calulatedFromMetrics);
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
        // If not an aggregation call, ie getting documents then set Sort if required
        if (! $this->isAggregationCall() && $this->getSort()) {
            $query->setSort($this->getSort());
        }

        // Create Dimensions and Metric Aggregations
        $dimensionAggregations = $this->createDimensionAggregations();
        $metricAggregations = $this->createMetricAggregations();

        // Combine Aggregations
        if ($this->isNestedDimensions && count($dimensionAggregations) > 1) {
            // Nested Aggregations
            $dimensionAggregations = $this->removeAllAggregation($dimensionAggregations);
            $firstDimensionAggregation = $dimensionAggregations[0];
            /** @var AbstractAggregation[] $dimensionAggregations */
            $dimensionAggregations = array_reverse($dimensionAggregations);
            /** @var \Elastica\Aggregation\AbstractAggregation $dimensionAggregation */
            foreach ($dimensionAggregations as $dimensionIndex => $dimensionAggregation) {
                // If Last Dimension
                if ($dimensionIndex == 0) {
                    foreach ($metricAggregations as $metricAggregation) {
                        $dimensionAggregation->addAggregation($metricAggregation);
                    }
                }
                if (isset($dimensionAggregations[$dimensionIndex + 1])) {
                    $dimensionAggregations[$dimensionIndex + 1]->addAggregation($dimensionAggregation);
                }
            }
            $query->addAggregation($firstDimensionAggregation);
        } elseif (count($dimensionAggregations) > 0) {
            // Top Level Aggregations
            foreach ($dimensionAggregations as $dimensionAggregation) {
                if ($dimensionAggregation instanceof AllAggregation) {
                    // All Aggregation is added to the top level
                    foreach ($metricAggregations as $metricAggregation) {
                        $query->addAggregation($metricAggregation);
                    }
                } else {
                    foreach ($metricAggregations as $metricAggregation) {
                        if ($dimensionAggregation instanceof Nested &&  $metricAggregation instanceof Nested) {
                            // If both Dimension & Metric is Nested, attach then so that they are in parent-child order
                            $dimensionAggregation->getSubAggregation()->addAggregation($metricAggregation->getSubAggregation());
                        } elseif ($dimensionAggregation instanceof Nested &&  ! $metricAggregation instanceof Nested) {
                            // If dimension is on nested document and we are getting metrics on parent doc, wrap metric in "Reverse Nested Aggregation"
                            $reverseNestedAggregation = new \Elastica\Aggregation\ReverseNested($metricAggregation->getName()."__ReverseNested");
                            $reverseNestedAggregation->addAggregation($metricAggregation);
                            $dimensionAggregation->getSubAggregation()->addAggregation($reverseNestedAggregation);
                        } else {
                            $dimensionAggregation->addAggregation($metricAggregation);
                        }
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
        if ($this->isDebug()) {
            print_r($query->toArray());
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

        $this->resultSet = new ResultSet($this, $search->search());
        return $this->resultSet;
    }

    /**
     * @return ResultSet
     */
    public function getResultSet() {
        return $this->resultSet;
    }

    /**
     * @return GoalSet
     */
    public function getGoalSet() {
        if (! $this->resultSet) {
            $this->execute();
        }
        if (empty($this->getGoals())) {
            return null;
        }
        return new GoalSet($this->getGoals(), $this->getResultSet());
    }

    /**
     * @return DimensionAggregateSet
     */
    public function getDimensionAggregateSet() {
        if (! $this->resultSet) {
            $this->execute();
        }
        return new DimensionAggregateSet($this->getResultSet());
    }

    /**
     * @return boolean
     */
    public function isDebug() {
        return $this->debug;
    }

    /**
     * @param boolean $debug
     * @return $this
     */
    public function setDebug($debug) {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @return bool
     */
    protected function isAggregationCall() {
        return count($this->getMetrics()) > 0 && count($this->getDimensions()) > 0;
    }
}