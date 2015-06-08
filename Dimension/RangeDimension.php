<?php

namespace Revinate\AnalyticsBundle\Dimension;

class RangeDimension extends Dimension {
    /** @var array() */
    protected $ranges = array();
    /** @var  string */
    protected $type = self::TYPE_NUMBER;

    /**
     * @param $name
     * @param null $field
     * @return self
     */
    public static function create($name, $field = null) {
        return new self($name, $field);
    }

    /**
     * @param array $range array("from" => int, "to" => int)
     * @return $this
     */
    public function addRange($range) {
        if (! isset($range["from"])) {
            $range["from"] = null;
        }
        if (! isset($range["to"])) {
            $range["to"] = null;
        }
        $this->ranges[] = $range;
        return $this;
    }

    /**
     * @return array
     */
    public function getRanges() {
        return $this->ranges;
    }

}
