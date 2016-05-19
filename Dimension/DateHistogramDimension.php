<?php

namespace Revinate\AnalyticsBundle\Dimension;

class DateHistogramDimension extends Dimension {
    /** @var string */
    protected $interval = 'week';
    /** @var string  */
    protected $format = 'yyyy-MM-dd';
    /** @var  string */
    protected $type = self::TYPE_DATE;

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
     * @param string $format
     * @return $this
     */
    public function setFormat($format) {
        $this->format = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat() {
        return $this->format;
    }

    /**
     * @return array
     */
    public function toArray() {
        return array_merge(parent::toArray(),
            array(
                'format' => $this->getFormat(),
                'interval' => $this->getInterval(),
            )
        );
    }
}
