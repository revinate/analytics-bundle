<?php

namespace Revinate\AnalyticsBundle;


use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\Dimension\DimensionInterface;
use Revinate\AnalyticsBundle\FilterSource\AbstractFilterSource;
use Revinate\AnalyticsBundle\Metric\Metric;
use Revinate\AnalyticsBundle\Metric\MetricInterface;


interface AnalyticsInterface {

    /**
     * @return Dimension[]
     */
    public function getDimensions();

    /**
     * @return Metric[]
     */
    public function getMetrics();

    /**
     * @return AbstractFilterSource[]
     */
    public function getFilterSources();

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