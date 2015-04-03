<?php

namespace Revinate\AnalyticsBundle;

use Revinate\AnalyticsBundle\Dimension\DateHistogramDimension;
use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\Dimension\DimensionInterface;
use Revinate\AnalyticsBundle\Filter\FilterInterface;
use Revinate\AnalyticsBundle\Metric\Metric;
use Revinate\AnalyticsBundle\Metric\MetricInterface;
use Revinate\AnalyticsBundle\Metric\ProcessedMetric;

interface AnalyticsInterface {

    /**
     * @return DimensionInterface[]
     */
    public function getDimensions();

    /**
     * @return MetricInterface[]
     */
    public function getMetrics();

    /**
     * @return FilterInterface[]
     */
    public function getFilters();

    /**
     * @return string
     */
    public function getIndex();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return mixed
     */
    public function getConfig();
}