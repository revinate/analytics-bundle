<?php

namespace Revinate\AnalyticsBundle\DimensionAggregate;

use Revinate\AnalyticsBundle\Analytics;
use Revinate\AnalyticsBundle\BaseAnalyticsInterface;
use Revinate\AnalyticsBundle\Metric\ProcessedMetric;
use Revinate\AnalyticsBundle\Result\AbstractResult;

class AverageDimensionAggregate implements DimensionAggregateInterface {

    /** @var Analytics */
    protected $analytics;

    /**
     * AverageDimensionAggregate constructor.
     * @param BaseAnalyticsInterface $analytics
     */
    public function __construct(BaseAnalyticsInterface $analytics) {
        $this->analytics = $analytics;
    }

    /**
     * @param $result
     * @param null $info
     * @return array
     */
    public function getAggregate($result, $info = null) {
        $agg = array();
        $this->calculate($result, $agg, $info);
        return $agg;
    }

    /**
     * @param $result
     * @param $agg
     * @param null $info
     */
    public function calculate($result, &$agg, $info = null) {
        foreach ($result as $bucketKey => $buckets) {
            if (AbstractResult::isArrayOfArrayOfScalars($buckets)) {
                $agg[$bucketKey]["average"] = array();
                $totals = array();
                $nonZeroCountsByKey = array();
                foreach ($buckets as $bucket) {
                    foreach ($bucket as $key => $value) {
                        if (AbstractResult::isInternalKey($key)) {
                            continue;
                        }
                        if (! isset($totals[$key])) {
                            $totals[$key] = 0;
                        }
                        if (! isset($nonZeroCountsByKey[$key])) {
                            $nonZeroCountsByKey[$key] = 0;
                        }
                        if ($value > 0) {
                            $nonZeroCountsByKey[$key]++;
                        }
                        $totals[$key] += $value;
                    }
                }
                foreach ($totals as $key => $total) {
                    $metric = $this->analytics->getMetric($key);
                    $weightValue = null;
                    $weight = null;
                    if ($metric instanceof ProcessedMetric) {
                        list($weightValue, $weight) = $metric->getWeightedParams();
                    }
                    if ($weightValue && $weight) {
                        $agg[$bucketKey]["average"][$key] = isset($totals[$weightValue]) && isset($totals[$weight]) && $totals[$weight] > 0 ? sprintf("%.".$metric->getPrecision()."f", $totals[$weightValue] / $totals[$weight]) : null;
                    } else {
                        $agg[$bucketKey]["average"][$key] = $nonZeroCountsByKey[$key] > 0 ? sprintf("%.".$metric->getPrecision()."f", $total / (is_null($info) ? $nonZeroCountsByKey[$key] : $info)) : null;
                    }
                }
            } else if(AbstractResult::isArrayOfArray($buckets)) {
                $agg[$bucketKey] = array();
                $this->calculate($buckets, $agg[$bucketKey]);
            }
        }
    }
}