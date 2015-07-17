<?php
namespace Revinate\AnalyticsBundle\Comparator;

interface ComparatorInterface {

    /**
     * @param $a
     * @param $b
     * @return float
     */
    public function calculate($a, $b);

}