<?php

namespace Revinate\AnalyticsBundle\Dimension;

/**
 * Class Dimension
 * @package Revinate\AnalyticsBundle\Dimension
 */
class Dimension implements DimensionInterface {
    const TYPE_STRING = 'string';
    const TYPE_NUMBER = 'number';
    const TYPE_DATE = 'date';

    /** @var string */
    protected $name;
    /** @var string */
    protected $field;
    /** @var  int */
    protected $size = 0;
    /** @var  string */
    protected $type = self::TYPE_STRING;

    /**
     * @param $name
     * @param null $field
     * @return Dimension
     */
    public static function create($name, $field = null) {
        return new self($name, $field);
    }

    /**
     * @param $name
     * @param $field
     */
    public function __construct($name, $field = null) {
        $this->name = $name;
        $this->field = $field ?: $name;
        return $this;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getField() {
        return $this->field;
    }

    /**
     * @return int
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @param $size
     * @return $this
     */
    public function setSize($size) {
        $this->size = $size;
        return $this;
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
     * @return array|mixed
     */
    public function toArray() {
        return array(
            'name' => $this->getName(),
        );
    }

    /**
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }
}