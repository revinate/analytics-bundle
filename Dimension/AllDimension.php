<?php

namespace Revinate\AnalyticsBundle\Dimension;

use Revinate\AnalyticsBundle\Aggregation\AllAggregation;

class AllDimension extends Dimension {

    /**
     * @param $name
     * @param null $field
     * @return self
     */
    public static function create($name = AllAggregation::NAME, $field = null) {
        return new self($name, $field);
    }
}
