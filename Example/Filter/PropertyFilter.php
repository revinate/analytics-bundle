<?php

namespace Revinate\AnalyticsBundle\Example\Filter;

use Doctrine\Entity;
use Revinate\AnalyticsBundle\Filter\AbstractMySQLFilter;
use Revinate\AnalyticsBundle\Filter\FilterInterface;
use Revinate\SharedBundle\Entity\Property;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PropertyFilter
 * @package Revinate\AnalyticsBundle\Example\Filter
 */
class PropertyFilter extends AbstractMySQLFilter {

    /**
     * @param ContainerInterface $container
     * @param $field
     * @return FilterInterface
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