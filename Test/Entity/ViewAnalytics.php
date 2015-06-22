<?php
namespace Revinate\AnalyticsBundle\Test\Entity;

use Revinate\AnalyticsBundle\Analytics;
use Revinate\AnalyticsBundle\AnalyticsInterface;
use Revinate\AnalyticsBundle\Dimension\AllDimension;
use Revinate\AnalyticsBundle\Dimension\DateHistogramDimension;
use Revinate\AnalyticsBundle\Dimension\DateRangeDimension;
use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\Dimension\DimensionInterface;
use Revinate\AnalyticsBundle\Dimension\HistogramDimension;
use Revinate\AnalyticsBundle\Dimension\RangeDimension;
use Revinate\AnalyticsBundle\FilterSource\AbstractFilterSource;
use Revinate\AnalyticsBundle\Metric\Metric;
use Revinate\AnalyticsBundle\Metric\MetricInterface;
use Revinate\AnalyticsBundle\Metric\Result;
use Revinate\AnalyticsBundle\Test\Elastica\FilterHelper;

class ViewAnalytics extends Analytics {

    const INDEX_NAME = "test_revinate_analytics_bundle";
    const INDEX_TYPE = "views";

    /**
     * @return DimensionInterface[]
     */
    public function getDimensions()
    {
        return array(
            AllDimension::create(),
            Dimension::create("browser"),
            Dimension::create("device"),
            DateHistogramDimension::create("dateHistogram", "date")->setInterval("month"),
            DateHistogramDimension::create("formattedDate", "date")->setInterval("month")->setFormat("yyyy/MM/dd"),
            DateRangeDimension::create("dateRange", "date")->addRange(array("to" => "now-1M/M"))->addRange(array("from" => "now-1M/M")),
            HistogramDimension::create("viewsHistogram", "views")->setInterval(10),
            RangeDimension::create("customRangeViews", "views")->addRange(array("to" => 5))->addRange(array("from" => 10)),
        );
    }

    /**
     * @return MetricInterface[]
     */
    public function getMetrics()
    {
        return array(
            Metric::create("totalViews", "views")->setResult(Result::SUM),
            Metric::create("uniqueViews", "views")->setResult(Result::COUNT),
            Metric::create("chromeTotalViews", "views")->setFilter(FilterHelper::getValueFilter("browser", "chrome"))->setResult(Result::SUM),
            Metric::create("ie6TotalViews", "views")->setFilter(FilterHelper::getValueFilter("browser", "ie6"))->setResult(Result::SUM),
            Metric::create("averageViews", "views")->setResult(Result::AVG),
        );
    }

    /**
     * @return AbstractFilterSource[]
     */
    public function getFilterSources()
    {
        return array();
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return self::INDEX_NAME;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::INDEX_TYPE;
    }
}