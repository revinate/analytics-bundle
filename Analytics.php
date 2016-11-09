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
     * @param $query
     * @param $page
     * @param $size
     * @return array
     * @internal param $attributes
     */
    public function getDimensionsArray($query, $page, $size) {
        $config = array();
        foreach ($this->getDimensions() as $dimension) {
            if (empty($query) || stripos($dimension->getName(), $query) !== false) {
                $config[] = $dimension->toArray();
            }
        }
        return array_slice($config, ($page - 1) * $size, $size);
    }

    /**
     * @param $query
     * @param $page
     * @param $size
     * @return array
     */
    public function getMetricsArray($query, $page, $size) {
        $config = array();
        foreach ($this->getMetrics() as $metric) {
            if (empty($query) || stripos($metric->getName(), $query) !== false) {
                $config[] = $metric->toArray();
            }
        }
        return array_slice($config, ($page - 1) * $size, $size);
    }

}