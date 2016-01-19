<?php

namespace Revinate\AnalyticsBundle\DimensionAggregate;

use Revinate\AnalyticsBundle\Result\ResultSet;

class DimensionAggregateSet {

    const TYPE_AVERGAE = "average";

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
    public function get($type, $info) {
        switch ($type) {
            case self::TYPE_AVERGAE:
                $avg = new AverageDimensionAggregate();
                return $avg->getAggregate($this->resultSet->getNested(), $info);
        }
        return array();
    }
}