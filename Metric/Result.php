<?php

namespace Revinate\AnalyticsBundle\Metric;

class Result {
    const COUNT = 'count';
    const AVG = 'avg';
    const SUM = 'sum';
    const MIN = 'min';
    const MAX = 'max';

    /**
     * @param $type
     * @return string
     * @throws \Exception
     */
    public static function getResultKey($type) {
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
            default:
                throw new \Exception(__METHOD__ . " Invalid type of metric result given: $type");
        }
    }
}