<?php

namespace Revinate\AnalyticsBundle\Dimension;
/**
 * Class DateRangeDimension
 * @package Revinate\AnalyticsBundle\Dimension
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-daterange-aggregation.html
 */
class DateRangeDimension extends RangeDimension {
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
            )
        );
    }
}
