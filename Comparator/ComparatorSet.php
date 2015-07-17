<?php

namespace Revinate\AnalyticsBundle\Comparator;

use Revinate\AnalyticsBundle\Result\ResultSet;

class ComparatorSet {

    /** @var  ResultSet[] */
    protected $resultSets;
    /** @var  string */
    protected $type;

    /**
     * @param $type
     * @param ResultSet[] $resultSets
     */
    function __construct($type, Array $resultSets) {
        $this->type = $type;
        $this->resultSets = $resultSets;
    }

    /**
     * @return array|mixed
     */
    public function getTabular() {
        return $this->get(ResultSet::TYPE_TABULAR);
    }

    /**
     * @return array
     */
    public function getFlattened() {
        return $this->get(ResultSet::TYPE_FLATTENED);
    }

    /**
     * @return array|mixed
     */
    public function getNested() {
        return $this->get(ResultSet::TYPE_NESTED);
    }

    /**
     * @return array
     */
    public function getRaw() {
        return $this->get(ResultSet::TYPE_RAW);
    }

    /**
     * @return mixed
     */
    public function getGoogleDataTable() {
        return $this->get(ResultSet::TYPE_GOOGLE_DATA_TABLE);
    }

    /**
     * @return mixed
     */
    public function getChartJs() {
        return $this->get(ResultSet::TYPE_CHART_JS);
    }

    /**
     * @param string $format
     * @return array
     */
    public function get($format = ResultSet::TYPE_NESTED) {
        $diffs = array();
        $resultSets = $this->resultSets;
        foreach ($resultSets as $index => $resultSet) {
            if (! isset($resultSets[$index+1])) { break; }
            $a = $resultSets[$index]->getResult($format);
            $b = $resultSets[$index+1]->getResult($format);
            $diffs[$index] = $this->getRecursiveDiff($a, $b);
        }
        return $diffs;
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return array|null
     */
    protected function getRecursiveDiff($a, $b) {
        $diff = is_array($a) ? array() : null;
        $allkeys = array_merge(array_keys($a), array_keys($b));
        foreach ($allkeys as $key) {
            if (is_array($this->value($a, $key)) || is_array($this->value($b, $key))) {
                $diff[$key] = $this->getRecursiveDiff($this->value($a, $key, array()), $this->value($b, $key, array()));
            } else {
                $diff[$key] = ComparatorFactory::get($this->type)->calculate($this->value($a, $key), $this->value($b, $key));
            }
        }
        return $diff;
    }


    /**
     * @param $array
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    protected function value($array, $key, $default = 0) {
        return isset($array[$key]) ? $array[$key] : $default;
    }


}