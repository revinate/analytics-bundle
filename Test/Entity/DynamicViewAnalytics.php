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
     * @return array
     */
    public function getDimensionsArray() {
        $config = array();
        foreach (array($this->getDimension("all"), $this->getDimension("browser"), $this->getDimension("site")) as $dimension) {
            /** @var Dimension $dimension */
            $config[] = $dimension->toArray();
        }
        return $config;
    }

    /**
     * @return array
     */
    public function getMetricsArray() {
        $config = array();
        foreach (array($this->getMetric("totalViews"), $this->getMetric("uniqueViews")) as $metric) {
            /** @var Metric $metric */
            $config[] = $metric->toArray();
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