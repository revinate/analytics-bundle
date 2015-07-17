<?php

namespace Revinate\AnalyticsBundle\Goal;

class Goal {
    /** @var  mixed */
    protected $value;
    /** @var string */
    protected $metric;

    /**
     * @param string $metric
     * @param mixed $value
     */
    function __construct($metric, $value) {
        $this->metric = $metric;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getMetric()
    {
        return $this->metric;
    }

}