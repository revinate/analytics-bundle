<?php

namespace Revinate\AnalyticsBundle\Dimension;
use Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface;

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
    /** @var  string */
    protected $readableName;
    /** @var string */
    protected $field;
    /** @var  int */
    protected $size = 0;
    /** @var  string */
    protected $type = self::TYPE_STRING;
    /** @var  bool */
    protected $returnEmpty = false;
    /** @var  string */
    protected $path;
    /** @var FilterSourceInterface */
    protected $filterSource;
    /** @var  array map of key value pairs */
    protected $attributes = array();

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
     * @param string $type
     * @return $this
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
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
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return FilterSourceInterface
     */
    public function getFilterSource() {
        return $this->filterSource;
    }

    /**
     * @return boolean
     */
    public function isReturnEmpty()
    {
        return $this->returnEmpty;
    }

    /**
     * @param boolean $returnEmpty
     * @return $this
     */
    public function setReturnEmpty($returnEmpty)
    {
        $this->returnEmpty = $returnEmpty;
        return $this;
    }

    /**
     * @param FilterSourceInterface $filterSource
     * @return $this
     */
    public function setFilterSource(FilterSourceInterface $filterSource) {
        $this->filterSource = $filterSource;
        return $this;
    }

    /**
     * @return array key value pairs
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * @param $key
     * @return string|null
     */
    public function getAttribute($key) {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes($attributes){
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function addAttribute($key, $value) {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * @param array $attrs key-value pairs of attributes
     * @return $this
     */
    public function addAttributes($attrs) {
        foreach ($attrs as $key => $value) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray() {
        return array(
            'name' => $this->getName(),
            'readableName' => $this->getReadableName(),
            'type' => $this->getType(),
            'attributes' => $this->getAttributes(),
            'filterSource' => $this->getFilterSource() ? $this->getFilterSource()->toArray() : null,
            'size' => $this->getSize(),
        );
    }
}