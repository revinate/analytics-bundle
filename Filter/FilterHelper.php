<?php

namespace Revinate\AnalyticsBundle\Filter;

use Revinate\AnalyticsBundle\Lib\DateHelper;

class FilterHelper {
    const TYPE_VALUE = 'value';
    const TYPE_RANGE = 'range';
    const TYPE_EXISTS = 'exists';
    const TYPE_MISSING = 'missing';
    const TYPE_PERIOD = "period";
    const TYPE_CUSTOM = 'custom';

    /**
     * @param string        $field
     * @param string|array  $value
     * @return \Elastica\Filter\Term|\Elastica\Filter\Terms
     */
    public static function getValueFilter($field, $value) {
        if (is_array($value)) {
            return new \Elastica\Filter\Terms($field, $value);
        } else {
            return new \Elastica\Filter\Term(array($field => $value));
        }
    }

    /**
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-filter.html
     * @param $field
     * @param array $range supported gte, lte, gt, lt
     * @return \Elastica\Filter\NumericRange
     */
    public static function getRangeFilter($field, array $range) {
        return new \Elastica\Filter\NumericRange($field, $range);
    }

    /**
     * @param $field
     * @param $period
     * @return \Elastica\Filter\Range
     * @throws \Exception
     */
    public static function getPeriodFilter($field, $period) {
        $periodInfo = DateHelper::getPeriodInfo($period);
        $startPeriod = date('c', strtotime($periodInfo["period"][0]));
        $endPeriod = date('c', strtotime($periodInfo["period"][2]." 23:59:59"));
        $range = array("gte" => $startPeriod, "lte" => $endPeriod);
        return new \Elastica\Filter\Range($field, $range);
    }

    /**
     * @param $field
     * @return \Elastica\Filter\Exists
     */
    public static function getExistsFilter($field) {
        return new \Elastica\Filter\Exists($field);
    }

    /**
     * @param $field
     * @return \Elastica\Filter\Missing
     */
    public static function getMissingFilter($field) {
        return new \Elastica\Filter\Missing($field);
    }
}