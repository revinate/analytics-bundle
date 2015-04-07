<?php

namespace Revinate\AnalyticsBundle\Example\FilterSource;


use Revinate\AnalyticsBundle\FilterSource\AbstractMySQLFilterSource;
use Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface;
use Revinate\SharedBundle\Entity\Property;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PropertyFilterSource
 * @package Revinate\AnalyticsBundle\Example\Filter
 */
class PropertyFilterSource extends AbstractMySQLFilterSource {

    /**
     * @param ContainerInterface $container
     * @param $field
     * @return FilterSourceInterface
     */
    public static function create(ContainerInterface $container, $field) {
        return new self($container, $field);
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
    public function getName() {
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