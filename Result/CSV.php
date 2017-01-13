<?php
namespace Revinate\AnalyticsBundle\Result;

class CSV extends AbstractResult {

    /**
     * @return mixed
     */
    public function getResult() {
        list($csv, $colkeys) = $this->buildResult($this->getNested());
        $cols = array();
        foreach ($colkeys as $index => $col) {
            $metric = $this->analytics->getMetric($col);
            if ($index == 0) {
                $cols[] = "Pivot";
            } else {
                $cols[] = $metric->getReadableName();
            }
        }
        array_unshift($csv, join(",", $cols));
        return join("\n", $csv);
    }

    /**
     * @param $data
     * @param $csv
     * @param $prefixKey
     * @return mixed
     */
    protected function buildResult($data, $csv = array(), $prefixKey = '') {
        $colkeys = array();
        foreach ($data as $key => $values) {
            if (! self::isArrayOfArray($values) && is_array($values)) {
                array_unshift($values, $this->getJoinedKey(array(ucfirst($prefixKey), $key), ": "));
                $csv[] = join(",", $values);
                $colkeys = array_keys($values);
            } else {
                list($csv2, $colkeys) = $this->buildResult($values, array(), $this->getJoinedKey(array($prefixKey, $key), ": "));
                $csv = array_merge($csv, $csv2);
            }
        }
        return array($csv, $colkeys);
    }
}