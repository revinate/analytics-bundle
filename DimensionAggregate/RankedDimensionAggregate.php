<?php

namespace Revinate\AnalyticsBundle\DimensionAggregate;

use Revinate\AnalyticsBundle\Result\AbstractResult;

class RankedDimensionAggregate implements DimensionAggregateInterface {

    public function getAggregate($result, $info = null) {
        $agg = array();
        $this->calculate($result, $agg, $info);
        return $agg;
    }

    /**
     * @param $result
     * @param $agg
     */
    public function calculate($result, &$agg, $info = null) {
        foreach ($result as $bucketKey => $buckets) {
            if (AbstractResult::isArrayOfArrayOfScalars($buckets)) {
                $agg[$bucketKey] = array();
                $keys = $this->getKeys($buckets);
                foreach ($keys as $key) {
                    uksort($buckets, $this->getCompareFunction($buckets, $key));
                    $rank = 1;
                    foreach ($buckets as $bucketKey2 => $bucket) {
                        $agg[$bucketKey][$bucketKey2][$key] = $rank++;
                        if (isset($bucket["_info"])) {
                            $agg[$bucketKey][$bucketKey2]["_info"] = $bucket["_info"];
                        }
                    }
                }
            } else if(AbstractResult::isArrayOfArray($buckets)) {
                $agg[$bucketKey] = array();
                $this->calculate($buckets, $agg[$bucketKey]);
            }
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function getKeys($data) {
        $keys = array();
        foreach ($data as $bucket) {
            foreach ($bucket as $key => $value) {
                if (AbstractResult::isInternalKey($key)) {
                    continue;
                }
                $keys[$key] = true;
            }
        }
        return array_keys($keys);
    }

    /**
     * @param array $buckets
     * @param string $key
     * @return \Closure
     */
    protected function getCompareFunction($buckets, $key) {
        return function($a, $b) use($buckets, $key) {
            $val1 = isset($buckets[$b][$key]) ? $buckets[$b][$key] : 0;
            $val2 = isset($buckets[$a][$key]) ? $buckets[$a][$key] : 0;
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