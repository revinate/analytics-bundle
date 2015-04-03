<?php

namespace Revinate\AnalyticsBundle\Example;

use Revinate\AnalyticsBundle\Analytics;
use Revinate\AnalyticsBundle\Dimension\AllDimension;
use Revinate\AnalyticsBundle\Dimension\DateHistogramDimension;
use Revinate\AnalyticsBundle\Dimension\DateRangeDimension;
use Revinate\AnalyticsBundle\Dimension\Dimension;
use Revinate\AnalyticsBundle\Dimension\HistogramDimension;
use Revinate\AnalyticsBundle\Dimension\RangeDimension;
use Revinate\AnalyticsBundle\Example\Filter\AppFilter;
use Revinate\AnalyticsBundle\Example\Filter\PropertyFilter;
use Revinate\AnalyticsBundle\Metric\Metric;
use Revinate\AnalyticsBundle\Metric\Result;

class GuestStayAnalytics extends Analytics {

    public function getDimensions() {
        return array(
            AllDimension::create(),
            Dimension::create("propertyId"),
            Dimension::create("channel"),
            DateHistogramDimension::create("checkinDate")->setInterval('month'),
            DateHistogramDimension::create("checkoutDate")->setInterval('month'),
            DateRangeDimension::create("checkoutDateRange")->addRange(array("to" => "now-5M/M"))->addRange(array("from" => "now-5M/M")),
            Dimension::create("roomType"),
            Dimension::create("tripType"),
            HistogramDimension::create("roomRate")->setInterval(1000),
            RangeDimension::create("roomRateRange", "roomRate")->addRange(array("to" => 3999))->addRange(array("from" => 4000)),
            Dimension::create("confirmationStatus"),
            Dimension::create("statusCode"),
            HistogramDimension::create("roomNumber")->setInterval(100),
            Dimension::create("gender"),
            Dimension::create("npsScore"),
            Dimension::create("associatedPromotionIds"),
            Dimension::create("country")
        );
    }

    /**
     * @return array|\Revinate\AnalyticsBundle\Filter\FilterInterface[]
     */
    public function getFilters() {
        return array(
            PropertyFilter::create($this->container, "propertyId"),
        );
    }

    public function getMetrics() {
        return array(
            Metric::create("guestStayCount", "_id")->setResult(Result::COUNT),
            Metric::create("avgRoomRate", "roomRate")->setResult(Result::AVG),
            Metric::create("avgNpsScore", "npsScore")->setResult(Result::AVG),
        );
    }

    public function getIndex() {
        return 'grm';
    }

    public function getType() {
        return 'guest_stay';
    }
}