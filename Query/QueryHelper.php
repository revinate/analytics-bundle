<?php

namespace Revinate\AnalyticsBundle\Query;

use Revinate\AnalyticsBundle\Lib\DateHelper;

class QueryHelper {
    const TYPE_VALUE = 'value';
    const TYPE_RANGE = 'range';
    const TYPE_EXISTS = 'exists';
    const TYPE_MISSING = 'missing';
    const TYPE_PERIOD = "period";
    const TYPE_CUSTOM = 'custom';

    /**
     * @param string        $field
     * @param string|array  $value
     * @return \Elastica\Query\Terms|\Elastica\Query\Terms
     */
    public static function getValueQuery($field, $value) {
        if (is_array($value)) {
            return new \Elastica\Query\Terms($field, $value);
        } else {
            return new \Elastica\Query\Term(array($field => $value));
        }
    }

    /**
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-filter.html
     * @param $field
     * @param array $range supported gte, lte, gt, lt
     * @return \Elastica\Query\Range
     */
    public static function getRangeQuery($field, array $range) {
        return new \Elastica\Query\Range($field, $range);
    }

    /**
     * @param $field
     * @param $period
     * @return \Elastica\Query\Range
     * @throws \Exception
     */
    public static function getPeriodQuery($field, $period) {
        $periodInfo = DateHelper::getPeriodInfo($period);
        $startPeriod = date('c', strtotime($periodInfo["period"][0]));
        $endPeriod = date('c', strtotime($periodInfo["period"][2]." 23:59:59"));
        $range = array("gte" => $startPeriod, "lte" => $endPeriod);
        return new \Elastica\Query\Range($field, $range);
    }

    /**
     * @param $field
     * @return \Elastica\Filter\Exists|\Elastica\Query\Exists
     */
    public static function getExistsQuery($field) {
        return new \Elastica\Query\Exists($field);
    }

    /**
     * @param $field
     * @return \Elastica\Filter\Missing|\Elastica\Query\Missing
     */
    public static function getMissingQuery($field) {
        return new \Elastica\Query\Missing($field);
    }

    /**
     * @return \Elastica\Query\Bool|\Elastica\Query\BoolQuery
     */
    public static function getBoolQuery() {
        return new \Elastica\Query\BoolQuery();
    }
}