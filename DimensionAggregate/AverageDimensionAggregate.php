<?php

namespace Revinate\AnalyticsBundle\DimensionAggregate;

use Revinate\AnalyticsBundle\Result\AbstractResult;

class AverageDimensionAggregate implements DimensionAggregateInterface {

    public function getAggregate($result) {
        $agg = array();
        $this->calculate($result, $agg);
        return $agg;
    }

    /**
     * @param $result
     * @param $agg
     */
    public function calculate($result, &$agg) {
        foreach ($result as $bucketKey => $buckets) {
            if (AbstractResult::isArrayOfArrayOfScalars($buckets)) {
                $agg[$bucketKey]["average"] = array();
                $nonZeroCountsByKey = array();
                foreach ($buckets as $bucket) {
                    foreach ($bucket as $key => $value) {
                        if (AbstractResult::isInternalKey($key)) {
                            continue;
                        }
                        if (! isset($agg[$bucketKey]["average"][$key])) {
                            $agg[$bucketKey]["average"][$key] = 0;
                        }
                        if (! isset($nonZeroCountsByKey[$key])) {
                            $nonZeroCountsByKey[$key] = 0;
                        }
                        if ($value > 0) {
                            $nonZeroCountsByKey[$key]++;
                        }
                        $agg[$bucketKey]["average"][$key] += $value;
                    }
                }
                foreach ($agg[$bucketKey]["average"] as $key => $avg) {
                    //$metric = $this->queryBuilder->getAnalytics()->getMetric($key);
                    $agg[$bucketKey]["average"][$key] = $nonZeroCountsByKey[$key] > 0 ? sprintf("%.2f",$avg / $nonZeroCountsByKey[$key]) : null;
                }
            } else if(AbstractResult::isArrayOfArray($buckets)) {
                $agg[$bucketKey] = array();
                $this->calculate($buckets, $agg[$bucketKey]);
            }
        }
    }
}