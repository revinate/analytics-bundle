<?php

namespace Revinate\AnalyticsBundle\DimensionAggregate;

use Revinate\AnalyticsBundle\Result\ResultSet;

class DimensionAggregateSet {

    const TYPE_AVERGAE = "average";
    const TYPE_RANKED = "ranked";
    const TYPE_RANKED_REVERSED = "ranked_reversed";

    /** @var ResultSet  */
    protected $resultSet;
    /** @var \Revinate\AnalyticsBundle\Query\QueryBuilder  */
    protected $queryBuilder;

    public function __construct(ResultSet $resultSet) {
        $this->resultSet = $resultSet;
        $this->queryBuilder = $this->resultSet->getQueryBuilder();
    }

    /**
     * @param $type
     * @return array
     */
    public function get($type, $info = null) {
        $agg = null;
        switch ($type) {
            case self::TYPE_AVERGAE:
                $agg = new AverageDimensionAggregate();
                break;
            case self::TYPE_RANKED:
                $agg = new RankedDimensionAggregate();
                break;
            case self::TYPE_RANKED_REVERSED:
                $agg = new RankedReversedDimensionAggregate();
                break;
        }
        return $agg ? $agg->getAggregate($this->resultSet->getNested(), $info) : array();
    }
}