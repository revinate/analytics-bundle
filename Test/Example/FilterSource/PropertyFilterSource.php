<?php

namespace Revinate\AnalyticsBundle\Test\Example\FilterSource;


use Revinate\AnalyticsBundle\FilterSource\AbstractMySQLFilterSource;
use Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface;
use Revinate\SharedBundle\Entity\Property;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PropertyFilterSource
 * @package Revinate\AnalyticsBundle\Test\Example\Filter
 */
class PropertyFilterSource extends AbstractMySQLFilterSource {

    /**
     * @param ContainerInterface $container
     * @param $name
     * @return FilterSourceInterface
     */
    public static function create(ContainerInterface $container, $name) {
        return new self($container, $name);
    }

    /**
     * @return string
     */
    public function getModel() {
        return "RevinateSharedBundle:Property";
    }

    /**
     * @return string
     */
    public function getReadableName() {
        return 'Property Filter';
    }

    /**
     * @param Property $entity
     * @return string
     */
    public function getEntityName($entity) {
        return $entity->getName();
    }

    /**
     * @param Property $entity
     * @return string
     */
    public function getEntityId($entity) {
        return $entity->getId();
    }
}