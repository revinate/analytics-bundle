<?php

namespace Revinate\AnalyticsBundle\Metric;

use Revinate\AnalyticsBundle\Analytics;
use Revinate\AnalyticsBundle\AnalyticsInterface;

class ProcessedMetric extends Metric {

    /** @var  \Closure */
    protected $postProcessCallback;
    /** @var  string[] */
    protected $calculatedFromMetrics = array();

    /**
     * @param string $name
     * @param null $field
     * @return ProcessedMetric
     */
    public static function create($name, $field = null) {
        return new self($name);
    }

    /**
     * @param $name
     */
    public function __construct($name) {
        parent::__construct($name, null);
        return $this;
    }

    /**
     * @param \string[] $calculatedFromMetrics
     * @param \Closure $postProcessCallback
     * @return $this
     */
    public function setCalculatedFromMetrics($calculatedFromMetrics, $postProcessCallback) {
        $this->calculatedFromMetrics = $calculatedFromMetrics;
        $this->postProcessCallback = $postProcessCallback;
        return $this;
    }

    /**
     * @return \string[]
     */
    public function getCalculatedFromMetrics() {
        return $this->calculatedFromMetrics;
    }

    /**
     * @return callable
     */
    public function getPostProcessCallback() {
        return $this->postProcessCallback;
    }

    /**
     * @param Analytics $analytics
     * @return bool
     * @throws \Exception
     */
    public function isDependentOnProcessedMetric(Analytics $analytics) {
        foreach ($this->calculatedFromMetrics as $metricName) {
            $metric = $analytics->getMetric($metricName);
            if ($metric instanceof ProcessedMetric) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Analytics $analytics
     * @return array
     * @throws \Exception
     */
    public function getCalculatedFromProcessedMetricsOnly(Analytics $analytics) {
        $processedMetrics = array();
        foreach ($this->calculatedFromMetrics as $metricName) {
            $metric = $analytics->getMetric($metricName);
            if ($metric instanceof ProcessedMetric) {
                $processedMetrics[] = $metricName;
            }
        }
        return $processedMetrics;
    }
}