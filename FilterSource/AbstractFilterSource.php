<?php

namespace Revinate\AnalyticsBundle\FilterSource;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractFilterSource implements FilterSourceInterface {
    const ALL = '_all';

    /** @var  string */
    protected $field;

    /** @var  ContainerInterface */
    protected $container;
    /**
     *
     */
    public function __construct(ContainerInterface $container, $field) {
        $this->container = $container;
        $this->field = $field;
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
    public function getField() {
        return $this->field;
    }

    /**
     * @return array
     */
    public function toArray() {
        return array(
            'name' => $this->getName(),
            "field" => $this->getField()
        );
    }
}