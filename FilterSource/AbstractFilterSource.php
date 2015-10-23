<?php

namespace Revinate\AnalyticsBundle\FilterSource;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractFilterSource implements FilterSourceInterface {
    const ALL = '_all';

    /** @var  string */
    protected $name;

    /** @var  ContainerInterface */
    protected $container;

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
     * @return array
     */
    public function toArray() {
        return array(
            'name' => $this->getReadableName(),
            "field" => $this->getName()
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