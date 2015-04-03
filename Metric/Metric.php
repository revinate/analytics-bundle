<?php

namespace Revinate\AnalyticsBundle\Metric;

/**
 * Class Metric
 * @package Revinate\AnalyticsBundle\Metric
 */
class Metric implements MetricInterface {

    /** @var string */
    protected $name;
    /** @var string  */
    protected $field;
    /** @var string */
    protected $result = 'sum';
    /** @var  string */
    protected $nestedPath;
    /** @var \Elastica\Filter\AbstractFilter */
    protected $filter;
    /** @var string */
    protected $default = 0;
    /** @var int  */
    protected $precision = 2;

    /**
     * @param $name
     * @param $field
     * @return Metric
     */
    public static function create($name, $field) {
        return new self($name, $field);
    }

    /**
     * @param $name
     * @param $field
     */
    public function __construct($name, $field) {
        $this->name = $name;
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getField() {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param $field
     * @return $this
     */
    public function setField($field) {
        $this->field = $field;
        return $this;
    }

    /**
     * @param $filter
     * @return $this
     */
    public function setFilter(\Elastica\Filter\AbstractFilter $filter) {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setNestedPath($path) {
        $this->nestedPath = $path;
        return $this;
    }

    /**
     * @return $this
     */
    public function setChildren() {
        return $this;
    }

    /**
     * @param $result
     * @return $this
     */
    public function setResult($result) {
        $this->result = $result;
        return $this;
    }

    /**
     * @return string
     */
    public function getResult() {
        return $this->result;
    }

    /**
     * @return array
     */
    public function getFilter() {
        return $this->filter;
    }

    /**
     * @return string
     */
    public function getNestedPath() {
        return $this->nestedPath;
    }

    /**
     * @param string $default
     * @return $this
     */
    public function setDefault($default) {
        $this->default = $default;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefault() {
        return $this->default;
    }

    /**
     * @param int $precision
     */
    public function setPrecision($precision) {
        $this->precision = $precision;
    }

    /**
     * @return int
     */
    public function getPrecision() {
        return $this->precision;
    }

    /**
     * @return array|mixed
     */
    public function toArray() {
        return array(
            'name' => $this->getName(),
        );
    }
}