<?php
/**
 * Created by PhpStorm.
 * User: vinay
 * Date: 5/12/16
 * Time: 3:36 PM
 */

namespace Revinate\AnalyticsBundle;

use Revinate\AnalyticsBundle\Filter\CustomFilterInterface;
use Revinate\AnalyticsBundle\FilterSource\AbstractFilterSource;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseAnalytics implements BaseAnalyticsInterface, AnalyticsViewInterface {
    /** @var  ContainerInterface */
    protected $container;
    /** @var  array */
    protected $context = array();

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @return CustomFilterInterface[]
     */
    public function getCustomFilters() {
        return array();
    }

    /**
     * @param $name
     * @throws \Exception
     * @return CustomFilterInterface
     */
    public function getCustomFilter($name) {
        foreach ($this->getCustomFilters() as $customFilter) {
            if ($customFilter->getName() == $name) {
                return $customFilter;
            }
        }
        throw new \Exception(__METHOD__ . " Invalid Custom Filter: " . $name);
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
     * @return array
     */
    public function getFilterSourcesArray() {
        $config = array();
        foreach ($this->getFilterSources() as $filterSource) {
            $config[] = $filterSource->toArray();
        }
        return $config;
    }

    /**
     * @return array
     */
    public function getCustomFiltersArray() {
        $config = array();
        foreach ($this->getCustomFilters() as $customFilter) {
            $config[] = $customFilter->toArray();
        }
        return $config;
    }

    /**
     * Gets Analytics Config
     * @param $page
     * @param $size
     * @return array
     */
    public function getConfig($page, $size) {
        $config = array();
        $config['dimensions'] = $this->getDimensionsArray($page, $size);
        $config['metrics'] = $this->getMetricsArray($page, $size);
        $config['filterSources'] = $this->getFilterSourcesArray();
        $config['customFilters'] = $this->getCustomFiltersArray();
        return $config;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer() {
        return $this->container;
    }

    /**
     * @return array
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext($context)  {
        $this->context = $context;
    }

    /**
     * @param $key
     * @return null
     */
    public function getContextValue($key) {
        return isset($this->context[$key]) ? $this->context[$key] : null;
    }
}