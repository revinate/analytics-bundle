<?php
namespace Revinate\AnalyticsBundle\Test\TestCase\Query;

use Revinate\AnalyticsBundle\Query\QueryBuilder;
use Revinate\AnalyticsBundle\Service\ElasticaService;
use Revinate\AnalyticsBundle\Test\Elastica\DocumentHelper;
use Revinate\AnalyticsBundle\Test\Elastica\FilterHelper;
use Revinate\AnalyticsBundle\Test\Entity\ViewAnalytics;
use Revinate\AnalyticsBundle\Test\TestCase\BaseTestCase;

class QueryBuilderTestCase extends BaseTestCase {

    protected function createData() {
        $docHelper = new DocumentHelper($this->type);
        $docHelper->createView("chrome", "ios", "-2 month", 6)
            ->createView("opera", "ios", "-3 month", 5)
            ->createView("opera", "ios", "-1 week", 2)
            ->createView("chrome", "android", "+0 day", 10)
        ;
        $docHelper->refresh();
    }

    protected function debug($results) {
        return "Results: " . print_r($results, true);
    }

    public function testDimensionsArePresent() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("all", "browser", "device"))
            ->addMetrics(array("totalViews", "uniqueViews"));
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertTrue(isset($results['device']) && isset($results['browser']) && isset($results['all']), $this->debug($results));
    }

    public function testMetricsArePresent() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("all"))
            ->addMetrics(array("totalViews", "uniqueViews"));
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertTrue(isset($results["all"]['totalViews']) && isset($results["all"]['uniqueViews']), $this->debug($results));
    }

    public function testResultFormats() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("device"))
            ->addMetrics(array("totalViews", "uniqueViews"));
        $resultSet = $querybuilder->execute();

        $nestedResults = $resultSet->getNested();
        $this->assertTrue(isset($nestedResults['device']['ios']['totalViews']), $this->debug($nestedResults));

        $rawResults = $resultSet->getRaw();
        $this->assertTrue(isset($rawResults['device']['buckets'][0]['totalViews']['value']), $this->debug($rawResults));
        $this->assertSame("ios", $rawResults['device']['buckets'][0]['key'], $this->debug($rawResults));

        $flattnedResults = $resultSet->getFlattened();
        $this->assertSame(13.0, $flattnedResults['device.ios.totalViews'], $this->debug($flattnedResults));
        $this->assertSame(10.0, $flattnedResults['device.android.totalViews'], $this->debug($flattnedResults));

        $googleTableResults = $resultSet->getGoogleDataTable();
        $this->assertSame('totalViews', $googleTableResults->cols[1]->label, $this->debug($googleTableResults));
        $this->assertSame('uniqueViews', $googleTableResults->cols[2]->label, $this->debug($googleTableResults));

        $this->assertSame('device.ios', $googleTableResults->rows[0]->c[0]->v, $this->debug($googleTableResults));
        $this->assertSame(13.0, $googleTableResults->rows[0]->c[1]->v, $this->debug($googleTableResults));
        $this->assertSame(3.0, $googleTableResults->rows[0]->c[2]->v, $this->debug($googleTableResults));

        $tabularResults = $resultSet->getTabular();
        $this->assertSame(13.0, $tabularResults['device.ios']['totalViews'], $this->debug($tabularResults));
        $this->assertSame(3.0, $tabularResults['device.ios']['uniqueViews'], $this->debug($tabularResults));
    }

    public function testBasicMetrics() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("all"))
            ->addMetrics(array("totalViews", "uniqueViews", "averageViews"));
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame(23.0, $results["all"]['totalViews'], $this->debug($results));
        $this->assertSame(4.0, $results["all"]['uniqueViews'], $this->debug($results));
        $this->assertSame(5.75, $results["all"]['averageViews'], $this->debug($results));
    }

    public function testFilteredMetrics() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("all"))
            ->addMetrics(array("chromeTotalViews", "ie6TotalViews"));
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame(16.0, $results["all"]['chromeTotalViews'], $this->debug($results));
        $this->assertSame(0.0, $results["all"]['ie6TotalViews'], $this->debug($results));
    }

    public function testBaseMetricsWithTopLevelFilter() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("all", "device"))
            ->addMetrics(array("totalViews", "uniqueViews", "averageViews"))
            ->setFilter(FilterHelper::getValueFilter("device", "ios"))
        ;
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();

        $this->assertSame(3.0, $results["all"]['uniqueViews'], $this->debug($results));
        $this->assertSame(13.0, $results["all"]['totalViews'], $this->debug($results));
        $this->assertSame(3.0, $results["device"]['ios']['uniqueViews'], $this->debug($results));
        $this->assertSame(13.0, $results["device"]['ios']['totalViews'], $this->debug($results));
        $this->assertArrayNotHasKey('android', $results["device"], $this->debug($results));
    }

    public function testBasicDimensions() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("all", "browser", "device"))
            ->addMetrics(array("totalViews"));
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame(13.0, $results["device"]["ios"]['totalViews'], $this->debug($results));
        $this->assertSame(10.0, $results["device"]["android"]['totalViews'], $this->debug($results));
        $this->assertSame(16.0, $results["browser"]["chrome"]['totalViews'], $this->debug($results));
        $this->assertSame(7.0, $results["browser"]["opera"]['totalViews'], $this->debug($results));
        $this->assertSame(23.0, $results["all"]['totalViews'], $this->debug($results));
    }

    public function testDateDimensions() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("dateHistogram", "formattedDate", "dateRange"))
            ->addMetrics(array("totalViews"));
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame(array(array("totalViews" => 11.0),array("totalViews" => 12.0)), array_values($results["dateRange"]), $this->debug($results));
        $this->assertSame(array(array("totalViews" => 5.0),array("totalViews" => 6.0),array("totalViews" => 0.0),array("totalViews" => 12.0)), array_values($results["dateHistogram"]), $this->debug($results));
        $this->assertSame(array(array("totalViews" => 5.0),array("totalViews" => 6.0),array("totalViews" => 0.0),array("totalViews" => 12.0)), array_values($results["formattedDate"]), $this->debug($results));
        $this->assertTrue(strpos(key($results['formattedDate']), '/') !== false, $this->debug($results));
    }

    public function testMetricDimensions() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("viewsHistogram", "customRangeViews"))
            ->addMetrics(array("totalViews"));
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame(array(array("totalViews" => 2.0),array("totalViews" => 10.0)), array_values($results["customRangeViews"]), $this->debug($results));
        $this->assertSame(array(array("totalViews" => 13.0),array("totalViews" => 10.0)), array_values($results["viewsHistogram"]), $this->debug($results));
    }

    public function testNestedDimensions() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("browser", "device"))
            ->addMetrics(array("totalViews"))
            ->setIsNestedDimensions(true);
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame(6.0, $results["browser"]["chrome"]["device"]["ios"]['totalViews'], $this->debug($results));
        $this->assertSame(10.0, $results["browser"]["chrome"]["device"]["android"]['totalViews'], $this->debug($results));
        $this->assertSame(7.0, $results["browser"]["opera"]["device"]["ios"]['totalViews'], $this->debug($results));
    }
}
