<?php

namespace Revinate\AnalyticsBundle\Test\Example\FilterSource;

use Revinate\AnalyticsBundle\FilterSource\AbstractMySQLFilterSource;
use Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface;
use Revinate\SharedBundle\Entity\App;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PropertyFilterSource
 * @package Revinate\AnalyticsBundle\Test\Example\Filter
 */
class AppFilterSource extends AbstractMySQLFilterSource {

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
        return "RevinateSharedBundle:App";
    }

    /**
     * @return string
     */
    public function getReadableName() {
        return 'App Filter';
    }

    protected function getNameColumn() {
        return "name";
    }
}