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
     *
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
}