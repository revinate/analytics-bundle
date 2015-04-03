<?php

namespace Revinate\AnalyticsBundle;

use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\Dimension\DimensionInterface;
use Revinate\AnalyticsBundle\Filter\AbstractFilter;
use Revinate\AnalyticsBundle\Filter\FilterInterface;
use Revinate\AnalyticsBundle\Metric\Metric;
use Revinate\AnalyticsBundle\Metric\MetricInterface;
use Revinate\AnalyticsBundle\Metric\ProcessedMetric;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Analytics implements AnalyticsInterface {

    /** @var  ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @param $name
     * @throws \Exception
     * @return Dimension
     */
    public function getDimension($name) {
        foreach ($this->getDimensions() as $dimension) {
            if ($dimension->getName() == $name) {
                return $dimension;
            }
        }
        throw new \Exception(__METHOD__ . " Invalid Dimension: " . $name);
    }

    /**
     * @param $name
     * @throws \Exception
     * @return Metric
     */
    public function getMetric($name) {
        foreach ($this->getMetrics() as $metric) {
            if ($metric->getName() == $name) {
                return $metric;
            }
        }
        throw new \Exception(__METHOD__ . " Invalid Metric: " . $name);
    }

    /**
     * @param $name
     * @throws \Exception
     * @return AbstractFilter
     */
    public function getFilter($name) {
        foreach ($this->getFilters() as $filter) {
            if ($filter->getName() == $name) {
                return $filter;
            }
        }
        throw new \Exception(__METHOD__ . " Invalid Filter: " . $name);
    }

    /**
     * @return string[]
     */
    public function getProcessedMetricNames() {
        $allMetrics = $this->getMetrics();
        $postProcessedMetrics = array();
        foreach ($allMetrics as $metric) {
            if ($metric instanceof ProcessedMetric) {
                $postProcessedMetrics[] = $metric->getName();
            }
        }
        return $postProcessedMetrics;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer() {
        return $this->container;
    }

    /**
     * Gets Analytics Config
     * @return array
     */
    public function getConfig() {
        $router = $this->container->get('router');
        $config = array(
            'dimensions' => array(),
            'metrics' => array(),
            'filters' => array(),
        );
        foreach ($this->getDimensions() as $dimension) {
            $config['dimensions'][] = $dimension->toArray();
        }
        foreach ($this->getMetrics() as $metric) {
            $config['metrics'][] = $metric->toArray();
        }
        foreach ($this->getFilters() as $filter) {
            $config['filters'][] = $filter->toArray();
        }
        return $config;
    }

}