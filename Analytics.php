<?php

namespace Revinate\AnalyticsBundle;

use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\Filter\CustomFilterInterface;
use Revinate\AnalyticsBundle\Filter\AnalyticsCustomFiltersInterface;
use Revinate\AnalyticsBundle\FilterSource\AbstractFilterSource;
use Revinate\AnalyticsBundle\Metric\Metric;
use Revinate\AnalyticsBundle\Metric\ProcessedMetric;
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
     * @return AbstractFilterSource
     */
    public function getFilterSource($name) {
        foreach ($this->getFilterSources() as $filterSource) {
            if ($filterSource->getName() == $name) {
                return $filterSource;
            }
        }
        throw new \Exception(__METHOD__ . " Invalid Filter: " . $name);
    }

    /**
     * @param $name
     * @throws \Exception
     * @return CustomFilterInterface
     */
    public function getCustomFilter($name) {
        if (! $this instanceof AnalyticsCustomFiltersInterface) {
            return null;
        }
        foreach ($this->getCustomFilters() as $customFilter) {
            if ($customFilter->getName() == $name) {
                return $customFilter;
            }
        }
        throw new \Exception(__METHOD__ . " Invalid Custom Filter: " . $name);
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
        $config = array(
            'dimensions' => array(),
            'metrics' => array(),
            'filterSources' => array(),
            'customFilters' => array(),
        );
        foreach ($this->getDimensions() as $dimension) {
            $config['dimensions'][] = $dimension->toArray();
        }
        foreach ($this->getMetrics() as $metric) {
            $config['metrics'][] = $metric->toArray();
        }
        foreach ($this->getFilterSources() as $filter) {
            $config['filterSources'][] = $filter->toArray();
        }
        if ($this instanceof AnalyticsCustomFiltersInterface) {
            foreach ($this->getCustomFilters() as $customFilter) {
                $config['customFilters'][] = $customFilter->toArray();
            }
        }
        return $config;
    }

}