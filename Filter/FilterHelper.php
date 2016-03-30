<?php

namespace Revinate\AnalyticsBundle\Filter;

use Revinate\AnalyticsBundle\Query\QueryHelper;

class FilterHelper {
    const TYPE_VALUE = 'value';
    const TYPE_RANGE = 'range';
    const TYPE_EXISTS = 'exists';
    const TYPE_MISSING = 'missing';
    const TYPE_PERIOD = "period";
    const TYPE_CUSTOM = 'custom';

    /**
     * @param $field
     * @param $value
     * @return \Elastica\Query\Terms
     */
    public static function getValueFilter($field, $value) {
        return QueryHelper::getValueQuery($field, $value);
    }

    /**
     * @param $field
     * @param array $range
     * @return \Elastica\Query\Range
     */
    public static function getRangeFilter($field, array $range) {
        return QueryHelper::getRangeQuery($field, $range);
    }

    /**
     * @param $field
     * @param $period
     * @return \Elastica\Query\Range
     */
    public static function getPeriodFilter($field, $period) {
        return QueryHelper::getPeriodQuery($field, $period);
    }

    /**
     * @param $field
     * @return \Elastica\Filter\Exists|\Elastica\Query\Exists
     */
    public static function getExistsFilter($field) {
        return QueryHelper::getExistsQuery($field);
    }

    /**
     * @param $field
     * @return \Elastica\Filter\Missing|\Elastica\Query\Missing
     */
    public static function getMissingFilter($field) {
        return QueryHelper::getMissingQuery($field);
    }
}