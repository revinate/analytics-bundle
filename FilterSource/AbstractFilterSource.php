<?php

namespace Revinate\AnalyticsBundle\FilterSource;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractFilterSource implements FilterSourceInterface {
    const ALL = '_all';
    /** @var  string */
    protected $name;
    /** @var  string */
    protected $field;
    /** @var  ContainerInterface */
    protected $container;
    /** @var  string */
    protected $type = FilterSourceType::VALUE;

    /**
     * @param ContainerInterface $container
     * @param $name
     */
    public function __construct(ContainerInterface $container, $name) {
        $this->container = $container;
        $this->name = $name;
        return $this;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer() {
        return $this->container;
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
     * @param string $field
     * @return $this
     */
    public function setField($field) {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
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
     * @return array
     */
    public function toArray() {
        return array(
            'name' => $this->getReadableName(),
            "key" => $this->getName(),
            "field" => $this->getField(),
            "type" => $this->getType(),
        );
    }

    /**
     * @param $data
     * NOTE: We might be overwriting
     */
    public function normalize($data) {
        if (isset($data[$this->getIdColumn()])) {
            $data["_id"] = $data[$this->getIdColumn()];
        }
        if (isset($data[$this->getNameColumn()])) {
            $data["_name"] = $data[$this->getNameColumn()];
        }
        return $data;
    }
}