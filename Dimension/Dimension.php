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
    /** @var  string */
    protected $path;
    /** @var FilterSourceInterface */
    protected $filterSource;
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
     * @param FilterSourceInterface $filterSource
     * @return $this
     */
    public function setFilterSource(FilterSourceInterface $filterSource) {
        $this->filterSource = $filterSource;
        return $this;
    }

    /**
     * @return array|mixed
     */
    public function toArray() {
        return array(
            'name' => $this->getName(),
            'readableName' => $this->getReadableName(),
        );
    }
}