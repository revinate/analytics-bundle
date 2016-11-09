<?php
namespace Revinate\AnalyticsBundle\Test\Entity;

use Revinate\AnalyticsBundle\BaseAnalytics;
use Revinate\AnalyticsBundle\Dimension\AllDimension;
use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface;
use Revinate\AnalyticsBundle\Metric\Metric;
use Revinate\AnalyticsBundle\Metric\Result;

class DynamicViewAnalytics extends BaseAnalytics {
    const INDEX_NAME = "test_revinate_analytics_bundle";
    const INDEX_TYPE = "views";

    /**
     * @param string $name
     * @return Dimension
     */
    public function getDimension($name) {
        $dimensionsInDatabase = array(
            "all" => AllDimension::create()->setReadableName("All Dimension")->setSize(0)->setType(Dimension::TYPE_STRING),
            "browser" => Dimension::create("browser"),
            "site" => Dimension::create("site", "siteId"),
        );
        return isset($dimensionsInDatabase[$name]) ? $dimensionsInDatabase[$name] : null;
    }

    /**
     * @param string $name
     * @return Metric
     */
    public function getMetric($name) {
        $metricsFromDatabase = array(
            "totalViews" => Metric::create("totalViews", "views")->setResult(Result::SUM),
            "uniqueViews" => Metric::create("uniqueViews", "views")->setResult(Result::COUNT),
        );
        return isset($metricsFromDatabase[$name]) ? $metricsFromDatabase[$name] : null;
    }


    /**
     * @param $query
     * @param $page
     * @param $size
     * @return array
     * @internal param $attributes
     */
    public function getDimensionsArray($query, $page, $size) {
        // Use $this->getContext() to return dimensions
        $config = array();
        foreach (array($this->getDimension("all"), $this->getDimension("browser"), $this->getDimension("site")) as $dimension) {
            /** @var Dimension $dimension */
            if (empty($query) || stripos($dimension->getName(), $query) !== false) {
                $config[] = $dimension->toArray();
            }
        }
        return $config;
    }

    /**
     * @param $query
     * @param $page
     * @param $size
     * @return array
     */
    public function getMetricsArray($query, $page, $size) {
        // Use $this->getContext() to return metrics
        $config = array();
        foreach (array($this->getMetric("totalViews"), $this->getMetric("uniqueViews")) as $metric) {
            /** @var Metric $metric */
            if (empty($query) || stripos($metric->getName(), $query) !== false) {
                $config[] = $metric->toArray();
            }
        }
        return $config;
    }

    /**
     * @return FilterSourceInterface[]
     */
    public function getFilterSources() {
        return array();
    }

    /**
     * @return string
     */
    public function getIndex() {
        return self::INDEX_NAME;
    }

    /**
     * @return string
     */
    public function getType() {
        return self::INDEX_TYPE;
    }
}