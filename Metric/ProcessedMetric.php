<?php

namespace Revinate\AnalyticsBundle\Metric;

class ProcessedMetric extends Metric {

    /** @var  \Closure */
    protected $postProcessCallback;
    /** @var  string[] */
    protected $calculatedFromMetrics;

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
}