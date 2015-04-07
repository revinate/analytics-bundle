<?php

namespace Revinate\AnalyticsBundle\Example\FilterSource;

use Revinate\AnalyticsBundle\FilterSource\AbstractMySQLFilterSource;
use Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface;
use Revinate\SharedBundle\Entity\App;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PropertyFilterSource
 * @package Revinate\AnalyticsBundle\Example\Filter
 */
class AppFilterSource extends AbstractMySQLFilterSource {

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