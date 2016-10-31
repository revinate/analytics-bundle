<?php

namespace Revinate\AnalyticsBundle\DimensionAggregate;

class RankedReversedDimensionAggregate extends RankedDimensionAggregate {

    /**
     * @param array $buckets
     * @param string $key
     * @return \Closure
     */
    protected function getCompareFunction($buckets, $key) {
        return function($a, $b) use($buckets, $key) {
            return (isset($buckets[$a][$key]) ? $buckets[$a][$key] : 0) - (isset($buckets[$b][$key]) ? $buckets[$b][$key] : 0);
        };
    }
}