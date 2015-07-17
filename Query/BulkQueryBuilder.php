<?php

namespace Revinate\AnalyticsBundle\Query;

use Revinate\AnalyticsBundle\Comparator\ComparatorFactory;
use Revinate\AnalyticsBundle\Comparator\ComparatorSet;
use Revinate\AnalyticsBundle\Result\ResultSet;

class BulkQueryBuilder {

    /** @var  QueryBuilder[] */
    protected $queryBuilders;
    /** @var ResultSet[] */
    protected $resultSets = array();

    /**
     * @param QueryBuilder $queryBuilder
     * @return $this
     */
    public function addQueryBuilder(QueryBuilder $queryBuilder) {
        $this->queryBuilders[] = $queryBuilder;
        return $this;
    }

    /**
     * @return ResultSet[]
     */
    public function execute() {
        foreach ($this->queryBuilders as $queryBuilder) {
            $this->resultSets[] = $queryBuilder->execute();
        }
        return $this->resultSets;
    }

    /**
     * @param $type
     * @return ComparatorSet
     */
    public function getComparatorSet($type) {
        if (count($this->resultSets) == 0) {
            $this->execute();
        }
        return new ComparatorSet($type, $this->getResultSets());
    }

    /**
     * @return \Revinate\AnalyticsBundle\Result\ResultSet[]
     */
    public function getResultSets()
    {
        return $this->resultSets;
    }

    /**
     * @return QueryBuilder[]
     */
    public function getQueryBuilders()
    {
        return $this->queryBuilders;
    }
}