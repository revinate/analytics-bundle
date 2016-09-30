<?php
namespace Revinate\AnalyticsBundle\Test\TestCase\Query;

use Revinate\AnalyticsBundle\Test\Elastica\DocumentHelper;
use Revinate\AnalyticsBundle\Test\Entity\ViewAnalytics;
use Revinate\AnalyticsBundle\Test\TestCase\BaseTestCase;

class DimensionTest extends BaseTestCase {
    protected function createData() {
        $docHelper = new DocumentHelper($this->type);
        $docHelper->createView("chrome", "ios", 1, "-2 month", 6)
            ->createView("opera", "ios", 7, "-3 month", 5)
            ->createView("opera", "ios", 1, "-1 week", 2)
            ->createView("chrome", "android", 4, "+0 day", 10)
            ->createView("opera", "ios", 8, "-1 week", null)
        ;
        $docHelper->refresh();
    }

    public function testDimensionAttributes() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $dimension = $viewAnalytics->getDimension("siteWithAttributes");
        $this->assertEquals($dimension->getAttribute("public"), true, $dimension->getAttributes());
        $this->assertEquals($dimension->getAttribute("type"), "attributed", $dimension->getAttributes());
        $this->assertEquals(count($dimension->getAttributes()), 2, $dimension->getAttributes());
    }

}
