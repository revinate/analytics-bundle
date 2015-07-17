<?php

namespace Revinate\AnalyticsBundle\Comparator;

use Revinate\AnalyticsBundle\Exception\InvalidComparatorTypeException;

class ComparatorFactory {

    /**
     * @param $type
     * @return ComparatorInterface
     * @throws InvalidComparatorTypeException
     */
    public static function get($type) {
        switch ($type) {
            case Change::TYPE:
                return new Change();
            case Index::TYPE:
                return new Index();
            case Percentage::TYPE:
                return new Percentage();
            default:
                throw new InvalidComparatorTypeException("No comparator found with type: $type");
        }
    }
}