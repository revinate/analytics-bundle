<?php
namespace Revinate\AnalyticsBundle\Comparator;

class Percentage implements ComparatorInterface {
    const TYPE = "percentage";

    /**
     * @param $a
     * @param $b
     * @return float
     */
    public function calculate($a, $b) {
        if (is_null($b)) {
            return "-";
        }
        $value = $a !== 0 ?
            $b / $a * 100 :
            100;
        return sprintf("%.2f%%", $value);
    }
}