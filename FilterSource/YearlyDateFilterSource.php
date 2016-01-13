<?php

namespace Revinate\AnalyticsBundle\FilterSource;

use Revinate\AnalyticsBundle\Lib\DateHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class YearlyDateFilterSource
 * @package Revinate\AnalyticsBundle\FilterSource
 */
class YearlyDateFilterSource extends AbstractDateFilterSource {

    /**
     * @param ContainerInterface $container
     * @param $name
     */
    public static function create(ContainerInterface $container, $name) {
        return new self($container, $name, DateHelper::SCALE_YEAR);
    }

}