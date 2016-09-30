<?php
namespace Revinate\AnalyticsBundle\Test\Entity;

use Revinate\AnalyticsBundle\Analytics;
use Revinate\AnalyticsBundle\Dimension\AllDimension;
use Revinate\AnalyticsBundle\Dimension\DateHistogramDimension;
use Revinate\AnalyticsBundle\Dimension\DateRangeDimension;
use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\Dimension\DimensionInterface;
use Revinate\AnalyticsBundle\Dimension\FiltersDimension;
use Revinate\AnalyticsBundle\Dimension\HistogramDimension;
use Revinate\AnalyticsBundle\Dimension\RangeDimension;
use Revinate\AnalyticsBundle\FilterSource\AbstractFilterSource;
use Revinate\AnalyticsBundle\Metric\Metric;
use Revinate\AnalyticsBundle\Metric\MetricInterface;
use Revinate\AnalyticsBundle\Metric\MetricType;
use Revinate\AnalyticsBundle\Metric\ProcessedMetric;
use Revinate\AnalyticsBundle\Metric\Result;
use Revinate\AnalyticsBundle\Filter\FilterHelper;
use Revinate\AnalyticsBundle\Test\Entity\FilterSource\SiteFilterSource;

class ViewAnalytics extends Analytics {

    const INDEX_NAME = "test_revinate_analytics_bundle";
    const INDEX_TYPE = "views";

    /**
     * @return DimensionInterface[]
     */
    public function getDimensions()
    {
        return array(
            AllDimension::create()->setReadableName("All Dimension")->setSize(0)->setType(Dimension::TYPE_STRING),
            Dimension::create("browser"),
            Dimension::create("site", "siteId")->setFilterSource($this->getFilterSource("siteId")),
            Dimension::create("siteWithAttributes", "siteId")->addAttributes(array("type" => "attributed"))->addAttribute("public", true),
            Dimension::create("allSite", "siteId")->setFilterSource($this->getFilterSource("siteId"))->setReturnEmpty(true),
            Dimension::create("device")->setReadableName("Device Type"),
            DateHistogramDimension::create("dateHistogram", "date")->setInterval("month"),
            DateHistogramDimension::create("dateHistogramWeekly", "date")->setInterval("week"),
            DateHistogramDimension::create("dateHistogramWithFormat", "date")->setInterval("month")->setFormat("yyyy:MM:dd"),
            DateHistogramDimension::create("formattedDate", "date")->setInterval("month")->setFormat("yyyy/MM/dd")->setType(Dimension::TYPE_DATE),
            DateRangeDimension::create("dateRange", "date")->addRange(array("to" => "now-1M/M"))->addRange(array("from" => "now-1M/M"))->setFormat("yyyy-MM-dd"),
            HistogramDimension::create("viewsHistogram", "views")->setInterval(10),
            RangeDimension::create("customRangeViews", "views")->addRange(array("to" => 5))->addRange(array("from" => 10))->setType(Dimension::TYPE_NUMBER),
            Dimension::create("tagName", "tags.name")->setPath("tags"),
            FiltersDimension::create("device_filtered")->addFilter(FilterHelper::getValueFilter("device", "ios"), "ios")->addFilter(FilterHelper::getValueFilter("device", "android"), "android"),
        );
    }

    /**
     * @return MetricInterface[]
     */
    public function getMetrics()
    {
        $dollarToRupeeConversionRate = $this->getContextValue("dollarToRupeeConversionRate");
        $activeBrowser = $this->getContextValue("browser");
        return array(
            Metric::create("totalViews", "views")->setResult(Result::SUM),
            Metric::create("uniqueViews", "views")->setResult(Result::COUNT)->setType(MetricType::AVERAGE),
            ProcessedMetric::create("viewDollarValue")->setCalculatedFromMetrics(array("totalViews"), function($totalViews) {
                return $totalViews > 0 ? $totalViews * 0.01 : null;
            })->setPrefix('$')->setPrecision(2),
            ProcessedMetric::create("viewRupeeValue")->setCalculatedFromMetrics(array("totalViews"), function($totalViews) use ($dollarToRupeeConversionRate) {
                return $totalViews * 0.01 * $dollarToRupeeConversionRate;
            })->setPrefix('Rs ')->setPrecision(2),
            ProcessedMetric::create("chromeViewsPct")->setCalculatedFromMetrics(array("totalViews", "chromeTotalViews"), function($totalViews, $chromeTotalViews) {
                return $totalViews > 0 ? $chromeTotalViews / $totalViews * 100 : null;
            })->setPostfix("%")->setPrecision(2)->setType(MetricType::PERCENTAGE),
            ProcessedMetric::create("chromeAndIe6Views")->setCalculatedFromMetrics(array("chromeTotalViews", "ie6TotalViews"), function($chromeTotalViews, $ie6TotalViews) {
                return $chromeTotalViews + $ie6TotalViews;
            })->setPrecision(2),
            ProcessedMetric::create("chromeAndIe6ViewDollarValue")->setCalculatedFromMetrics(array("chromeAndIe6Views"), function($chromeAndIe6Views) {
                return $chromeAndIe6Views * 0.05;
            })->setPrecision(2),
            Metric::create("chromeTotalViews", "views")->setFilter(FilterHelper::getValueFilter("browser", "chrome"))->setResult(Result::SUM),
            Metric::create("browserTotalViews", "views")->setFilter(FilterHelper::getValueFilter("browser", $activeBrowser))->setResult(Result::SUM)->setReadableName("Total Views For $activeBrowser"),
            Metric::create("ie6TotalViews", "views")->setFilter(FilterHelper::getValueFilter("browser", "ie6"))->setResult(Result::SUM),
            Metric::create("averageViews", "views")->setResult(Result::AVG),
            Metric::create("averageWeightage", "tags.weightage")->setNestedPath("tags")->setResult(Result::AVG),
            Metric::create("averageWeightageForVip", "tags.weightage")->setNestedPath("tags")->setFilter(FilterHelper::getValueFilter("tags.name", "vip"))->setResult(Result::AVG),
            // Misc Random Result types
            Metric::create("maxViews", "views")->setResult(Result::MAX),
            Metric::create("minViews", "views")->setResult(Result::MIN),
            Metric::create("badViewsMetric", "views")->setResult("WrongResultType"),
        );
    }

    /**
     * @return AbstractFilterSource[]
     */
    public function getFilterSources()
    {
        return array(
            SiteFilterSource::create($this->container, "siteId")
        );
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