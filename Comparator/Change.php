<?php
namespace Revinate\AnalyticsBundle\Comparator;

class Change implements ComparatorInterface {
    const TYPE = "change";

    /**
     * @param $a
     * @param $b
     * @return float
     */
    public function calculate($a, $b) {
        if (is_null($b)) { return "-"; }
        $value = $a !== 0 ?
            ($b - $a) / $a * 100 :
            100;
        return sprintf("%.2f%%", $value);
    }
}