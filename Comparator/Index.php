<?php
namespace Revinate\AnalyticsBundle\Comparator;

class Index implements ComparatorInterface {
    const TYPE = "index";

    /**
     * @param $a
     * @param $b
     * @return float
     */
    public function calculate($a, $b) {
        $value = $a !== 0 ?
            (($b - $a) / $a * 100) :
            100;
        return round($value, 2) + 100;
    }
}