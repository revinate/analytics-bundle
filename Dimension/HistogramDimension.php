<?php

namespace Revinate\AnalyticsBundle\Dimension;

class HistogramDimension extends Dimension {
    /** @var string */
    protected $interval = 1000;
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
     * @param mixed $interval
     * @return $this
     */
    public function setInterval($interval) {
        $this->interval = $interval;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInterval() {
        return $this->interval;
    }

    /**
     * @return array
     */
    public function toArray() {
        return array_merge(parent::toArray(),
            array(
                'interval' => $this->getInterval(),
            )
        );
    }
}
