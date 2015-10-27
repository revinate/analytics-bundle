<?php

namespace Revinate\AnalyticsBundle\Test\Example;

use Revinate\AnalyticsBundle\Analytics;
use Revinate\AnalyticsBundle\Dimension\AllDimension;
use Revinate\AnalyticsBundle\Dimension\DateHistogramDimension;
use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\Filter\FilterHelper;
use Revinate\AnalyticsBundle\Test\Example\FilterSource\AppFilterSource;
use Revinate\AnalyticsBundle\Test\Example\FilterSource\PropertyFilterSource;
use Revinate\AnalyticsBundle\Metric\Metric;
use Revinate\AnalyticsBundle\Metric\ProcessedMetric;
use Revinate\AnalyticsBundle\Metric\Result;


class LogEventAnalytics extends Analytics {

    /**
     * @return array|\Revinate\AnalyticsBundle\Dimension\DimensionInterface[]
     */
    public function getDimensions() {
        return array(
            AllDimension::create(),
            Dimension::create("propertyId"),
            Dimension::create("project"),
            Dimension::create("type"),
            Dimension::create("status"),
            DateHistogramDimension::create("date")->setInterval('week'),
        );
    }

    /**
     * @return array|\Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface[]
     */
    public function getFilterSources() {
        return array(
            AppFilterSource::create($this->container, "appId"),
            PropertyFilterSource::create($this->container, "propertyId"),
        );
    }

    /**
     * @return array|\Revinate\AnalyticsBundle\Metric\MetricInterface[]
     */
    public function getMetrics() {
        return array(
            Metric::create("totalEvents", "count")->setResult(Result::SUM),
            Metric::create("uniqueEvents", "_id")->setResult(Result::COUNT),
            Metric::create("totalPromotionEmails", "count")->setFilter(FilterHelper::getValueFilter("type", "promotion.email"))->setResult(Result::SUM),
            Metric::create("guestWebViews", "count")->setFilter(FilterHelper::getValueFilter("type", "guest.web.view"))->setResult(Result::SUM),
            Metric::create("guestAppViews", "count")->setFilter(FilterHelper::getValueFilter("type", "guest.app.view"))->setResult(Result::SUM),
            Metric::create("userPageViews", "count")->setFilter(FilterHelper::getValueFilter("type", "user.page.view"))->setResult(Result::SUM),
            Metric::create("userPageVisits", "count")->setFilter(FilterHelper::getValueFilter("type", "user.page.visit"))->setResult(Result::SUM),
            ProcessedMetric::create("userPageViewsPerVisit")
                ->setCalculatedFromMetrics(array("userPageViews", "userPageVisits"), function($userPageViews, $userPageVisits) {
                    return $userPageVisits > 0 ? $userPageViews / $userPageVisits : 0;
                }
            ),
        );
    }

    public function getIndex() {
        return 'log_event_*';
    }

    public function getType() {
        return 'log_event';
    }
}