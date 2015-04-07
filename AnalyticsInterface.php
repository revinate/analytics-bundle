<?php

namespace Revinate\AnalyticsBundle;


use Revinate\AnalyticsBundle\Dimension\DimensionInterface;
use Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface;

use Revinate\AnalyticsBundle\Metric\MetricInterface;


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
     * @return FilterSourceInterface[]
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