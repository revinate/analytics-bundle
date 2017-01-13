<?php

namespace Revinate\AnalyticsBundle\DimensionAggregate;

use Revinate\AnalyticsBundle\Query\QueryBuilder;
use Revinate\AnalyticsBundle\Result\AbstractResult;

class RankedDimensionAggregate implements DimensionAggregateInterface {

    /** @var QueryBuilder */
    protected $qb;

    /**
     * AverageDimensionAggregate constructor.
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb) {
        $this->qb = $qb;
    }

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
        $requestedMetrics = $this->qb->getMetrics();
        foreach ($result as $bucketKey => $buckets) {
            if (AbstractResult::isArrayOfArrayOfScalars($buckets)) {
                $agg[$bucketKey] = array();
                $keys = $this->getKeys($buckets);
                foreach ($keys as $key) {
                    if (!in_array($key, $requestedMetrics)) {
                        continue;
                    }
                    $sortData = array();
                    foreach ($buckets as $bucketKey2 => $bucket) {
                        // array_multisort: Associative (string) keys will be maintained, but numeric keys will be re-indexed.
                        $sortData["_".$bucketKey2][$key] = $bucket[$key];
                    }
                    $sortData = $this->array_orderby($sortData, $key, $this->getOrder());
                    $rank = 1;
                    foreach ($sortData as $bucketKey2 => $bucket) {
                        $bucketKey2 = substr($bucketKey2, 1);
                        $agg[$bucketKey][$bucketKey2][$key] = $rank++;
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
     * @return int
     */
    protected function getOrder() {
        return SORT_DESC;
    }

    /**
     * Code taken from http://php.net/manual/en/function.array-multisort.php#100534
     * @return mixed
     */
    function array_orderby() {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row) {
                    $tmp[$key] = isset($row[$field]) ? $row[$field] : 0;
                }
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }
}