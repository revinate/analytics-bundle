<?php

namespace Revinate\AnalyticsBundle\Result;

use Revinate\AnalyticsBundle\Query\QueryBuilder;

interface ResultInterface {

    /**
     * @param \Revinate\AnalyticsBundle\Query\QueryBuilder $queryBuilder
     * @param \Elastica\ResultSet $elasticaResultSet
     */
    public function __construct(QueryBuilder $queryBuilder, \Elastica\ResultSet $elasticaResultSet);

    /**
     * @return mixed
     */
    public function getResult();
}