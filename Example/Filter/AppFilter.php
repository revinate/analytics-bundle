<?php

namespace Revinate\AnalyticsBundle\Example\Filter;

use Revinate\AnalyticsBundle\Filter\AbstractMySQLFilter;
use Revinate\AnalyticsBundle\Filter\FilterInterface;
use Revinate\SharedBundle\Entity\App;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PropertyFilter
 * @package Revinate\AnalyticsBundle\Example\Filter
 */
class AppFilter extends AbstractMySQLFilter {

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
        return "RevinateSharedBundle:App";
    }

    /**
     * @return string
     */
    public function getName() {
        return 'App Filter';
    }

    /**
     * @param App $entity
     * @return string
     */
    public function getEntityName($entity) {
        return $entity->getName();
    }

    /**
     * @param App $entity
     * @return string
     */
    public function getEntityId($entity) {
        return $entity->getId();
    }

}