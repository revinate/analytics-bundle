<?php

namespace Revinate\AnalyticsBundle\Test\Example;

use Revinate\AnalyticsBundle\Analytics;
use Revinate\AnalyticsBundle\Dimension\AllDimension;
use Revinate\AnalyticsBundle\Dimension\DateHistogramDimension;
use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\Filter\FilterHelper;
use Revinate\AnalyticsBundle\Test\Example\FilterSource\AppFilterSource;
use Revinate\AnalyticsBundle\Metric\Metric;

use Revinate\AnalyticsBundle\Metric\Result;

class AppRequestAnalytics extends Analytics {

    public function getDimensions() {
        return array(
            AllDimension::create(),
            Dimension::create("stayId"),
            Dimension::create("appId"),
            Dimension::create("deviceType"),
            Dimension::create("sourceType"),
            Dimension::create("assignedTo"),
            Dimension::create("resolvedBy"),
            DateHistogramDimension::create("createdAt")->setInterval('week'),
            DateHistogramDimension::create("resolvedAt")->setInterval('week'),
        );
    }

    /**
     * @return array|\Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface[]
     */
    public function getFilterSources() {
        return array(
            AppFilterSource::create($this->container, "appId"),
        );
    }

    public function getMetrics() {
        return array(
            Metric::create("totalRequests", "count")->setResult(Result::SUM),
            Metric::create("totalEntityRequests", "count")->setResult(Result::SUM)->setNestedPath('entities'),
            Metric::create("totalRevenue", "revenue")->setResult(Result::SUM)->setNestedPath('entities'),
            Metric::create("avgOpenTimeInSecs", "openTimeInSecs")->setResult(Result::AVG)->setNestedPath('entities')->setFilter(FilterHelper::getValueFilter("appId", 1)),
        );
    }

    public function getIndex() {
        return 'app_request_*';
    }

    public function getType() {
        return 'app_request';
    }
}