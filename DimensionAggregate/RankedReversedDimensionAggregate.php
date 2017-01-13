<?php

namespace Revinate\AnalyticsBundle\DimensionAggregate;

class RankedReversedDimensionAggregate extends RankedDimensionAggregate {
    /**
     * @return int
     */
    protected function getOrder() {
        return SORT_ASC;
    }
}