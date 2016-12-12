<?php

namespace Revinate\AnalyticsBundle\Metric;

use Revinate\AnalyticsBundle\BaseAnalyticsInterface;

class ProcessedMetric extends Metric {

    /** @var  \Closure */
    protected $postProcessCallback;
    /** @var  string[] */
    protected $calculatedFromMetrics = array();
    /** @var string metric value which is used to calculated weighted average */
    protected $weightedValueMetric = null;
    /** @var string metric which is used as a weight to calculate weighted average */
    protected $weightedWeightMetric = null;

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
     * @param BaseAnalyticsInterface $analytics
     * @return bool
     * @throws \Exception
     */
    public function isDependentOnProcessedMetric(BaseAnalyticsInterface $analytics) {
        foreach ($this->calculatedFromMetrics as $metricName) {
            $metric = $analytics->getMetric($metricName);
            if ($metric && $metric instanceof ProcessedMetric) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param BaseAnalyticsInterface $analytics
     * @return array
     * @throws \Exception
     */
    public function getCalculatedFromProcessedMetricsOnly(BaseAnalyticsInterface $analytics) {
        $processedMetrics = array();
        foreach ($this->calculatedFromMetrics as $metricName) {
            $metric = $analytics->getMetric($metricName);
            if ($metric && $metric instanceof ProcessedMetric) {
                $processedMetrics[] = $metricName;
            }
        }
        return $processedMetrics;
    }

    /**
     * Set the metric weight parameters
     * @param string $valueMetric
     * @param string $weightMetric
     * @return $this
     */
    public function setWeightedParams($valueMetric, $weightMetric) {
        $this->weightedValueMetric = $valueMetric;
        $this->weightedWeightMetric = $weightMetric;
        return $this;
    }

    /**
     * @return array
     */
    public function getWeightedParams() {
        return array($this->weightedValueMetric, $this->weightedWeightMetric);
    }
}