<?php

namespace Revinate\AnalyticsBundle\DimensionAggregate;

interface DimensionAggregateInterface {
    /**
     * @param $result
     * @return array
     */
    public function getAggregate($result);
}