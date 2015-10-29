<?php

namespace Revinate\AnalyticsBundle\Metric;

/**
 * Class Metric
 * @package Revinate\AnalyticsBundle\Metric
 */
class Metric implements MetricInterface {

    /** @var string */
    protected $name;
    /** @var  string */
    protected $readableName;
    /** @var string  */
    protected $field;
    /** @var string */
    protected $result = 'sum';
    /** @var  string */
    protected $nestedPath;
    /** @var \Elastica\Filter\AbstractFilter */
    protected $filter;
    /** @var string */
    protected $default = null;
    /** @var int  */
    protected $precision = 1;
    /** @var string */
    protected $prefix = "";
    /** @var string  */
    protected $postfix = "";

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
     * @param $data
     * @return mixed
     */
    public function getValue($data) {
        $value = isset($data[$this->getResultKey()]) ? $data[$this->getResultKey()] : $this->getDefault();
        return $value ? sprintf("%s%." . $this->getPrecision() . "f%s", $this->getPrefix(), $value, $this->getPostfix()) : null;
    }

    /**
     * @param $type
     * @return bool
     */
    public function isResultOfType($type) {
        return $this->getResult() == $type;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getResultKey() {
        return Result::getResultKey($this->getResult());
    }

    /**
     * @return \Elastica\Filter\AbstractFilter
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
     * @return $this
     */
    public function setPrecision($precision) {
        $this->precision = $precision;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrecision() {
        return $this->precision;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        // Return sprintf compatible prefix
        return $this->prefix;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostfix()
    {
        return $this->postfix;
    }

    /**
     * @param string $postfix
     * @return $this
     */
    public function setPostfix($postfix)
    {
        $this->postfix = $postfix;
        return $this;
    }

    /**
     * @param string $readableName
     * @return $this
     */
    public function setReadableName($readableName)
    {
        $this->readableName = $readableName;
        return $this;
    }

    /**
     * @return string
     */
    public function getReadableName() {
        return $this->readableName ?: ucwords(preg_replace('/([A-Z]+)/', ' $1', $this->getName()));
    }

    /**
     * @return array|mixed
     */
    public function toArray() {
        return array(
            'name' => $this->getName(),
            'readableName' => $this->getReadableName(),
            'prefix' => $this->getPrefix(),
            'postfix' => $this->getPostfix(),
            'precision' => $this->getPrecision()
        );
    }
}