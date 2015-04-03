<?php

namespace Revinate\AnalyticsBundle\Result;

use Revinate\AnalyticsBundle\Query\QueryBuilder;

interface ResultInterface {

    /**
     * @param \Revinate\AnalyticsBundle\Query\QueryBuilder $queryBuilder
     * @param $data
     */
    public function __construct(QueryBuilder $queryBuilder, $data);

    /**
     * @return mixed
     */
    public function getResult();
}