<?php

namespace Revinate\AnalyticsBundle\Metric;

class Result {
    const COUNT = 'count';
    const AVG = 'avg';
    const SUM = 'sum';
    const MIN = 'min';
    const MAX = 'max';
    const SUM_OF_SQUARES = 'sum_of_squares';
    const VARIANCE = 'variance';
    const STD_DEVIATION = 'std_deviation';

    /**
     * @param $type
     * @return string
     * @throws \Exception
     */
    public static function getResultKey ($type) {
        switch ($type) {
            case self::COUNT:
                return "value";
            case self::AVG:
                return "value";
            case self::SUM:
                return "value";
            case self::MIN:
                return "min";
            case self::MAX:
                return "max";
            case self::SUM_OF_SQUARES:
                return "sum_of_squares";
            case self::VARIANCE:
                return "variance";
            case self::STD_DEVIATION:
                return "std_deviation";
            default:
                throw new \Exception(__METHOD__ . " Invalid type of metric result given: $type");
        }
    }
}