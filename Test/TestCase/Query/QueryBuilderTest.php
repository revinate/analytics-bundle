<?php
namespace Revinate\AnalyticsBundle\Test\TestCase\Query;

use Revinate\AnalyticsBundle\Comparator\Change;
use Revinate\AnalyticsBundle\Comparator\Index;
use Revinate\AnalyticsBundle\Comparator\Percentage;
use Revinate\AnalyticsBundle\Comparator\Value;
use Revinate\AnalyticsBundle\Exception\InvalidComparatorTypeException;
use Revinate\AnalyticsBundle\Goal\Goal;
use Revinate\AnalyticsBundle\Query\BulkQueryBuilder;
use Revinate\AnalyticsBundle\Query\QueryBuilder;
use Revinate\AnalyticsBundle\Result\ResultSet;
use Revinate\AnalyticsBundle\Test\Elastica\DocumentHelper;
use Revinate\AnalyticsBundle\Filter\FilterHelper;
use Revinate\AnalyticsBundle\Test\Entity\ViewAnalytics;
use Revinate\AnalyticsBundle\Test\TestCase\BaseTestCase;

class QueryBuilderTestCase extends BaseTestCase {

    protected function createData() {
        $docHelper = new DocumentHelper($this->type);
        $docHelper->createView("chrome", "ios", 1, "-2 month", 6)
            ->createView("opera", "ios", 7, "-3 month", 5)
            ->createView("opera", "ios", 1, "-1 week", 2)
            ->createView("chrome", "android", 4, "+0 day", 10)
        ;
        $docHelper->refresh();
    }

    protected function debug($results) {
        return "Results: " . print_r($results, true);
    }

    public function testConfig() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $config = $viewAnalytics->getConfig();
        $this->assertTrue(count($config['dimensions']) > 0);
        $this->assertTrue(count($config['metrics']) > 0);
    }

    public function testDimensionsArePresent() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("all", "browser"))->addDimension("device")
            ->addMetrics(array("totalViews"))->addMetric("uniqueViews");
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
        $this->assertSame('13.0', $flattnedResults['device.ios.totalViews'], $this->debug($flattnedResults));
        $this->assertSame('10.0', $flattnedResults['device.android.totalViews'], $this->debug($flattnedResults));

        $googleTableResults = $resultSet->getGoogleDataTable();
        $this->assertSame('totalViews', $googleTableResults->cols[1]->label, $this->debug($googleTableResults));
        $this->assertSame('uniqueViews', $googleTableResults->cols[2]->label, $this->debug($googleTableResults));
        $this->assertSame('device.ios', $googleTableResults->rows[0]->c[0]->v, $this->debug($googleTableResults));
        $this->assertSame(13.0, $googleTableResults->rows[0]->c[1]->v, $this->debug($googleTableResults));
        $this->assertSame(3.0, $googleTableResults->rows[0]->c[2]->v, $this->debug($googleTableResults));

        $tabularResults = $resultSet->getTabular();
        $this->assertSame('13.0', $tabularResults['device.ios']['totalViews'], $this->debug($tabularResults));
        $this->assertSame('3.0', $tabularResults['device.ios']['uniqueViews'], $this->debug($tabularResults));

        $chartJsResults = $resultSet->getChartJs();
        $this->assertSame('device.ios', $chartJsResults['labels'][0], $this->debug($chartJsResults));
        $this->assertSame('13.0', $chartJsResults['datasets'][0]['data'][0], $this->debug($chartJsResults));
        $this->assertSame('3.0', $chartJsResults['datasets'][1]['data'][0], $this->debug($chartJsResults));
    }

    public function testResultFormatsFromGetResultsMethod() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("device"))
            ->addMetrics(array("totalViews", "uniqueViews"));
        $resultSet = $querybuilder->execute();

        $nestedResults = $resultSet->getResult(ResultSet::TYPE_NESTED);
        $this->assertTrue(isset($nestedResults['device']['ios']['totalViews']), $this->debug($nestedResults));

        $rawResults = $resultSet->getResult(ResultSet::TYPE_RAW);
        $this->assertTrue(isset($rawResults['device']['buckets'][0]['totalViews']['value']), $this->debug($rawResults));
        $this->assertSame("ios", $rawResults['device']['buckets'][0]['key'], $this->debug($rawResults));

        $flattnedResults = $resultSet->getResult(ResultSet::TYPE_FLATTENED);
        $this->assertSame('13.0', $flattnedResults['device.ios.totalViews'], $this->debug($flattnedResults));
        $this->assertSame('10.0', $flattnedResults['device.android.totalViews'], $this->debug($flattnedResults));

        $googleTableResults = $resultSet->getResult(ResultSet::TYPE_GOOGLE_DATA_TABLE);
        $this->assertSame('totalViews', $googleTableResults->cols[1]->label, $this->debug($googleTableResults));
        $this->assertSame('uniqueViews', $googleTableResults->cols[2]->label, $this->debug($googleTableResults));
        $this->assertSame('device.ios', $googleTableResults->rows[0]->c[0]->v, $this->debug($googleTableResults));
        $this->assertSame(13.0, $googleTableResults->rows[0]->c[1]->v, $this->debug($googleTableResults));
        $this->assertSame(3.0, $googleTableResults->rows[0]->c[2]->v, $this->debug($googleTableResults));

        $tabularResults = $resultSet->getResult(ResultSet::TYPE_TABULAR);
        $this->assertSame('13.0', $tabularResults['device.ios']['totalViews'], $this->debug($tabularResults));
        $this->assertSame('3.0', $tabularResults['device.ios']['uniqueViews'], $this->debug($tabularResults));

        $chartJsResults = $resultSet->getResult(ResultSet::TYPE_CHART_JS);
        $this->assertSame('device.ios', $chartJsResults['labels'][0], $this->debug($chartJsResults));
        $this->assertSame('13.0', $chartJsResults['datasets'][0]['data'][0], $this->debug($chartJsResults));
        $this->assertSame('3.0', $chartJsResults['datasets'][1]['data'][0], $this->debug($chartJsResults));
    }

    public function testBasicMetrics() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("all"))
            ->addMetrics(array("totalViews", "uniqueViews", "averageViews", "chromeViewsPct", "viewDollarValue", "maxViews", "minViews"));
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame('23.0', $results["all"]['totalViews'], $this->debug($results));
        $this->assertSame('4.0', $results["all"]['uniqueViews'], $this->debug($results));
        $this->assertSame('5.8', $results["all"]['averageViews'], $this->debug($results));
        $this->assertSame('69.57%', $results["all"]['chromeViewsPct'], $this->debug($results));
        $this->assertSame('$0.23', $results["all"]['viewDollarValue'], $this->debug($results));
        $this->assertSame('10.0', $results["all"]['maxViews'], $this->debug($results));
        $this->assertSame('2.0', $results["all"]['minViews'], $this->debug($results));
    }

    public function testBasicDocuments() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder
            ->setOffset(0)
            ->setSize(4)
            ;
        $results = $querybuilder->execute()->getDocuments();
        $this->assertSame(4, count($results), $this->debug($results));
        $this->assertSame('ios', $results[0]['device'], $this->debug($results));
        $this->assertSame('chrome', $results[0]['browser'], $this->debug($results));
        $this->assertSame(6, $results[0]['views'], $this->debug($results));
    }

    public function testBasicDocumentsWithSort() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder
            ->setOffset(0)
            ->setSize(4)
            ->setSort(array("views" => "desc"));
        $results = $querybuilder->execute()->getDocuments();
        $this->assertSame(4, count($results), $this->debug($results));
        $this->assertSame('android', $results[0]['device'], $this->debug($results));
        $this->assertSame('chrome', $results[0]['browser'], $this->debug($results));
        $this->assertSame(10, $results[0]['views'], $this->debug($results));
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidResultType() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("all"))
            ->addMetrics(array("badViewsMetric"));
        // Check for Exception
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
    }

    public function testFilteredMetrics() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("all"))
            ->addMetrics(array("chromeTotalViews", "ie6TotalViews"));
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame('16.0', $results["all"]['chromeTotalViews'], $this->debug($results));
        $this->assertSame('0.0', $results["all"]['ie6TotalViews'], $this->debug($results));
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

        $this->assertSame('3.0', $results["all"]['uniqueViews'], $this->debug($results));
        $this->assertSame('13.0', $results["all"]['totalViews'], $this->debug($results));
        $this->assertSame('3.0', $results["device"]['ios']['uniqueViews'], $this->debug($results));
        $this->assertSame('13.0', $results["device"]['ios']['totalViews'], $this->debug($results));
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
        $this->assertSame('13.0', $results["device"]["ios"]['totalViews'], $this->debug($results));
        $this->assertSame('10.0', $results["device"]["android"]['totalViews'], $this->debug($results));
        $this->assertSame('16.0', $results["browser"]["chrome"]['totalViews'], $this->debug($results));
        $this->assertSame('7.0', $results["browser"]["opera"]['totalViews'], $this->debug($results));
        $this->assertSame('23.0', $results["all"]['totalViews'], $this->debug($results));
    }

    public function testDateDimensions() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("dateHistogram", "formattedDate", "dateRange"))
            ->addMetrics(array("totalViews"));
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame(array(array("totalViews"=> '11.0'),array("totalViews"=> '12.0')), array_values($results["dateRange"]), $this->debug($results));
        //$this->assertSame(array(array("totalViews"=> '5.0'),array("totalViews"=> '6.0'),array("totalViews"=> '0.0'),array("totalViews"=> '12.0')), array_values($results["dateHistogram"]), $this->debug($results));
        $this->assertSame(array(array("totalViews"=> '5.0'),array("totalViews"=> '6.0'),array("totalViews"=> '0.0'),array("totalViews"=> '12.0')), array_values($results["formattedDate"]), $this->debug($results));
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
        $this->assertSame(array(array("totalViews"=> '2.0'),array("totalViews"=> '10.0')), array_values($results["customRangeViews"]), $this->debug($results));
        $this->assertSame(array(array("totalViews"=> '13.0'),array("totalViews"=> '10.0')), array_values($results["viewsHistogram"]), $this->debug($results));
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
        $this->assertSame('6.0', $results["browser"]["chrome"]["device"]["ios"]['totalViews'], $this->debug($results));
        $this->assertSame('10.0', $results["browser"]["chrome"]["device"]["android"]['totalViews'], $this->debug($results));
        $this->assertSame('7.0', $results["browser"]["opera"]["device"]["ios"]['totalViews'], $this->debug($results));
    }

    /**
     * @expectedException \Revinate\AnalyticsBundle\Exception\InvalidComparatorTypeException
     */
    public function testBulkQuery() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $bulkQueryBuilder = new BulkQueryBuilder();

        $querybuilder1 = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder1
            ->addDimensions(array("browser", "device"))
            ->addMetrics(array("totalViews", "uniqueViews"))
            ->setFilter(FilterHelper::getValueFilter("browser", "chrome"))
        ;
        $querybuilder2 = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder2
            ->addDimensions(array("browser", "device"))
            ->addMetrics(array("totalViews"))
            ->setFilter(FilterHelper::getValueFilter("browser", "opera"))
        ;
        $querybuilder3 = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder3
            ->addDimensions(array("browser", "device", "all"))
            ->addMetrics(array("totalViews"))
        ;

        $bulkQueryBuilder
            ->addQueryBuilder($querybuilder1)
            ->addQueryBuilder($querybuilder2)
            ->addQueryBuilder($querybuilder3)
        ;

        $comparatorSet = $bulkQueryBuilder->getComparatorSet(Percentage::TYPE);
        $compResults = $comparatorSet->get(ResultSet::TYPE_NESTED);
        $this->assertSame("100.00%", $compResults[0]["browser"]["opera"]["totalViews"], $this->debug($compResults));
        $this->assertSame("116.67%", $compResults[0]["device"]["ios"]["totalViews"], $this->debug($compResults));
        $this->assertSame("185.71%", $compResults[1]["device"]["ios"]["totalViews"], $this->debug($compResults));
        $this->assertSame("100.00%", $compResults[1]["all"]["totalViews"], $this->debug($compResults));

        $comparatorSet = $bulkQueryBuilder->getComparatorSet(Change::TYPE);
        $compResults = $comparatorSet->get(ResultSet::TYPE_NESTED);
        $this->assertSame("100.00%", $compResults[0]["browser"]["opera"]["totalViews"], $this->debug($compResults));
        $this->assertSame("16.67%", $compResults[0]["device"]["ios"]["totalViews"], $this->debug($compResults));
        $this->assertSame("85.71%", $compResults[1]["device"]["ios"]["totalViews"], $this->debug($compResults));
        $this->assertSame("100.00%", $compResults[1]["all"]["totalViews"], $this->debug($compResults));

        $comparatorSet = $bulkQueryBuilder->getComparatorSet(Index::TYPE);
        $compResults = $comparatorSet->getNested();
        $this->assertSame(200.0, $compResults[0]["browser"]["opera"]["totalViews"], $this->debug($compResults));
        $this->assertSame(116.67, $compResults[0]["device"]["ios"]["totalViews"], $this->debug($compResults));
        $this->assertSame(185.71, $compResults[1]["device"]["ios"]["totalViews"], $this->debug($compResults));
        $this->assertSame(200.0, $compResults[1]["all"]["totalViews"], $this->debug($compResults));

        // check for InvalidComparatorException Exception
        $comparatorSet = $bulkQueryBuilder->getComparatorSet("wrong-type");
        $compResults = $comparatorSet->getNested();
    }

    public function testGoals() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $goals = array(
            new Goal("totalViews", 10),
            new Goal("uniqueViews", 2)
        );
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder
            ->addDimensions(array("browser", "device"))
            ->addMetrics(array("totalViews", "uniqueViews"))
            ->setFilter(FilterHelper::getValueFilter("browser", "chrome"))
            ->setGoals($goals)
        ;
        $goalSet = $querybuilder->getGoalSet();
        $goalResults = $goalSet->get(ResultSet::TYPE_NESTED);
        $this->assertSame("100.00%", $goalResults["device"]["android"]["totalViews"], $this->debug($goalResults));
        $this->assertSame("50.00%", $goalResults["device"]["android"]["uniqueViews"], $this->debug($goalResults));
        $this->assertSame("60.00%", $goalResults["device"]["ios"]["totalViews"], $this->debug($goalResults));
        $this->assertSame("160.00%", $goalResults["browser"]["chrome"]["totalViews"], $this->debug($goalResults));
    }

    public function testNestedAndReverseNestedDimensionAndMetrics() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder
            ->addDimensions(array("tagName", "device"))
            ->addMetrics(array("averageWeightage", "totalViews"))
        ;
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame("13.0", $results["device"]["ios"]["totalViews"], $this->debug($results));
        $this->assertSame("10.0", $results["device"]["android"]["totalViews"], $this->debug($results));
        $this->assertSame("23.0", $results["tagName"]["new"]["totalViews"], $this->debug($results));
        $this->assertSame("23.0", $results["tagName"]["vip"]["totalViews"], $this->debug($results));

        $this->assertSame("3.5", $results["device"]["ios"]["averageWeightage"], $this->debug($results));
        $this->assertSame("3.5", $results["device"]["android"]["averageWeightage"], $this->debug($results));
        $this->assertSame("3.0", $results["tagName"]["new"]["averageWeightage"], $this->debug($results));
        $this->assertSame("4.0", $results["tagName"]["vip"]["averageWeightage"], $this->debug($results));
    }

    /**
     * Note: only works with ES 1.4+
     */
    public function xtestDimensionFiltersAndMetrics() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder
            ->addDimensions(array("device_filtered", "device"))
            ->addMetrics(array("uniqueViews", "totalViews"))
        ;
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame(json_encode($results["device_filtered"]), json_encode($results["device"]), $this->debug($results));
    }

    public function testProcessedMetricsFromOtherProcessedMetrics() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder
            ->addDimensions(array("all", "device"))
            ->addMetrics(array("chromeAndIe6ViewDollarValue"))
        ;
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame("0.80", $results["all"]["chromeAndIe6ViewDollarValue"], $this->debug($results));
        $this->assertSame("0.30", $results["device"]["ios"]["chromeAndIe6ViewDollarValue"], $this->debug($results));
        $this->assertSame("0.50", $results["device"]["android"]["chromeAndIe6ViewDollarValue"], $this->debug($results));
    }

    public function testFilterSource() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $filterSource = $viewAnalytics->getFilterSource("siteId");
        $site = $filterSource->get(1);
        $this->assertSame("google.com", $site["_name"], $this->debug($site));

        $sites = $filterSource->getByQuery("oo", 1, 10);
        $this->assertSame(3, count($sites), $this->debug($sites));
    }

    public function testDimensionsKeysWithValues() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder
            ->addDimensions(array("device", "site"))
            ->addMetrics(array("totalViews", "uniqueViews"))
        ;
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame(1, $results["site"][1]["_info"]["_id"], $this->debug($results));
        $this->assertSame("google.com", $results["site"][1]["_info"]["_name"], $this->debug($results));
        $this->assertSame("google", $results["site"][1]["_info"]["slug"], $this->debug($results));
        $this->assertSame(false, isset($results["device"]["ios"]["_info"]), $this->debug($results));
    }

    public function testNullFilledDimensions() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder
            ->addDimensions(array("device", "site"))
            ->addMetrics(array("totalViews", "uniqueViews"))
            ->setIsNestedDimensions(true)
        ;
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame(10, count($results["device"]["ios"]["site"]), $this->debug($results));
        $this->assertSame("yahoo.com", $results["device"]["ios"]["site"][2]["_info"]["_name"], $this->debug($results));
        $this->assertFalse(isset($results["device"]["ios"]["site"][2]["totalViews"]), $this->debug($results));
    }

    public function testContextBasedMetrics() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $viewAnalytics->setContext(array(
            "dollarToRupeeConversionRate" => 62
        ));
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder
            ->addDimensions(array("all"))
            ->addMetrics(array("viewRupeeValue", "viewDollarValue"))
        ;
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();
        $this->assertSame("$0.23", $results["all"]["viewDollarValue"], $this->debug($results));
        $this->assertSame("Rs 14.26", $results["all"]["viewRupeeValue"], $this->debug($results));
    }

    public function testDimensionsWithOrder() {
        $this->createData();
        $viewAnalytics = new ViewAnalytics($this->getContainer());
        $querybuilder = new QueryBuilder($this->elasticaClient, $viewAnalytics);
        $querybuilder->addDimensions(array("browser", "device", "dateRange", "tagName"))
            ->addMetrics(array("totalViews", "uniqueViews"))
            ->setSort(array("totalViews" => "asc"))
        ;
        $resultSet = $querybuilder->execute();
        $results = $resultSet->getNested();

        $firstDeviceResult = array_shift($results["device"]);
        $firstBrowserResult = array_shift($results["browser"]);
        $this->assertSame('10.0', $firstDeviceResult['totalViews'], $this->debug($results));
        $this->assertSame('7.0', $firstBrowserResult['totalViews'], $this->debug($results));
    }
}
