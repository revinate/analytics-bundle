<?php
/**
 * Created by PhpStorm.
 * User: vinay
 * Date: 5/12/16
 * Time: 4:22 PM
 */

namespace Revinate\AnalyticsBundle;

use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\Filter\CustomFilterInterface;
use Revinate\AnalyticsBundle\FilterSource\AbstractFilterSource;
use Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface;
use Revinate\AnalyticsBundle\Metric\Metric;

interface BaseAnalyticsInterface {
    /**
     * @param string $name
     * @return Dimension
     */
    public function getDimension($name);

    /**
     * @param string $name
     * @return Metric
     */
    public function getMetric($name);

    /**
     * @param string $name
     * @return AbstractFilterSource
     */
    public function getFilterSource($name);

    /**
     * @param $name
     * @return CustomFilterInterface
     */
    public function getCustomFilter($name);

    /**
     * @return FilterSourceInterface[]
     */
    public function getFilterSources();

    /**
     * @return CustomFilterInterface[]
     */
    public function getCustomFilters();

    /**
     * @param $page
     * @param $size
     * @return array
     */
    public function getDimensionsArray($page, $size);

    /**
     * @param $page
     * @param $size
     * @return array
     */
    public function getMetricsArray($page, $size);

    /**
     * @return array
     */
    public function getCustomFiltersArray();

    /**
     * @return string
     */
    public function getIndex();

    /**
     * @return string
     */
    public function getType();

    /**
     * @param $page
     * @param $size
     * @return mixed
     */
    public function getConfig($page, $size);

    /**
     * @return mixed
     */
    public function getContext();

    /**
     * @param array $context
     */
    public function setContext($context);

    /**
     * @param $key
     * @return null
     */
    public function getContextValue($key);

}