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
            $val1 = isset($buckets[$a][$key]) ? $buckets[$a][$key] : 0;
            $val2 = isset($buckets[$b][$key]) ? $buckets[$b][$key] : 0;
            if ($val1 > $val2) {
                return 1;
            } else if ($val1 < $val2) {
                return -1;
            } else {
                return 0;
            }
        };
    }
}