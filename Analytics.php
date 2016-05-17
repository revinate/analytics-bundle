<?php

namespace Revinate\AnalyticsBundle;

use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\Metric\Metric;

abstract class Analytics extends BaseAnalytics implements AnalyticsInterface {

    /**
     * @param $name
     * @throws \Exception
     * @return Dimension
     */
    public function getDimension($name) {
        foreach ($this->getDimensions() as $dimension) {
            if ($dimension->getName() == $name) {
                return $dimension;
            }
        }
        throw new \Exception(__METHOD__ . " Invalid Dimension: " . $name);
    }

    /**
     * @param $name
     * @throws \Exception
     * @return Metric
     */
    public function getMetric($name) {
        foreach ($this->getMetrics() as $metric) {
            if ($metric->getName() == $name) {
                return $metric;
            }
        }
        throw new \Exception(__METHOD__ . " Invalid Metric: " . $name);
    }

    /**
     * @return array
     */
    public function getDimensionsArray() {
        $config = array();
        foreach ($this->getDimensions() as $dimension) {
            $config[] = $dimension->toArray();
        }
        return $config;
    }

    /**
     * @return array
     */
    public function getMetricsArray() {
        $config = array();
        foreach ($this->getMetrics() as $metric) {
            $config[] = $metric->toArray();
        }
        return $config;
    }

}