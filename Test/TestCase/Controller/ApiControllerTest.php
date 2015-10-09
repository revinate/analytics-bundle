<?php
namespace Revinate\AnalyticsBundle\Test\TestCase\Controller;

use Revinate\AnalyticsBundle\Test\Elastica\DocumentHelper;
use Revinate\AnalyticsBundle\Test\TestCase\BaseTestCase;
use Revinate\AnalyticsBundle\Test\TestCase\BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\BrowserKit\Tests\TestClient;

class ApiControllerTest extends BaseTestCase
{
    /** @var TestClient */
    protected $client;

    protected function setUp() {
        parent::setUp();
        $this->client = static::createClient(array(), array('HTTP_HOST' => '127.0.0.1:8999'));
    }

    protected function createData() {
        $docHelper = new DocumentHelper($this->type);
        $docHelper->createView("chrome", "ios", 2, "-2 month", 6)
            ->createView("opera", "ios", 8, "-3 month", 5)
            ->createView("opera", "ios", 6, "-1 week", 2)
            ->createView("chrome", "android", 2, "+0 day", 10)
        ;
        $docHelper->refresh();
    }

    protected function debug($response) {
        return "Response: " . print_r($response, true);
    }

    public function testListSourceApi() {
        $this->client->request("GET", "/api/analytics/source");
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame("view", key($response), $this->debug($response));
    }

    public function testGetSourceApi() {
        $this->client->request("GET", "/api/analytics/source/view");
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(!empty($response['dimensions']), $this->debug($response));
        $this->assertTrue(!empty($response['metrics']), $this->debug($response));
    }

    public function testStatsSourceApi() {
        $this->createData();
        $post = json_encode(array(
            "dimensions" => array("all", "device"),
            "metrics" => array("totalViews", "uniqueViews", "averageViews"),
            "filters" => array(),
            "flags" => array("nestedDimensions" => false),
            "format" => "nested"
        ));
        $this->client->request("POST", "/api/analytics/source/view/stats", array(), array(), array(), $post);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('23.0', $response["all"]['totalViews'], $this->debug($response));
        $this->assertSame('4.0', $response["all"]['uniqueViews'], $this->debug($response));
        $this->assertSame('5.8', $response["all"]['averageViews'], $this->debug($response));
    }

    public function testBulkStatsSourceApi() {
        $this->createData();
        $post = json_encode(
            array(
                'queries' => array(
                    array(
                        "dimensions" => array("all", "device"),
                        "metrics" => array("totalViews", "uniqueViews", "averageViews"),
                        "filters" => array(),
                    ),
                    array(
                        "dimensions" => array("all", "device", "browser"),
                        "metrics" => array("totalViews", "uniqueViews", "averageViews"),
                        "filters" => array(),
                    )
                ),
                "flags" => array("nestedDimensions" => false),
                "format" => "nested"
            )
        );
        $this->client->request("POST", "/api/analytics/source/view/bulkstats", array(), array(), array(), $post);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('23.0', $response['results'][0]['all']['totalViews'], $this->debug($response));
        $this->assertSame('13.0', $response['results'][0]['device']['ios']['totalViews'], $this->debug($response));
        $this->assertSame('23.0', $response['results'][1]['all']['totalViews'], $this->debug($response));
        $this->assertSame('16.0', $response['results'][1]['browser']['chrome']['totalViews'], $this->debug($response));
    }

    public function testBulkStatsSourceApiWithComparator() {
        $this->createData();
        $post = json_encode(
            array(
                'queries' => array(
                    array(
                        "dimensions" => array("all", "device", "browser"),
                        "metrics" => array("totalViews", "uniqueViews", "averageViews"),
                        "filters" => array(
                            "browser" => array("value", "chrome")
                        ),
                    ),
                    array(
                        "dimensions" => array("all", "device", "browser"),
                        "metrics" => array("totalViews", "uniqueViews", "averageViews"),
                        "filters" => array(
                            "browser" => array("value", "opera")
                        ),
                    ),
                    array(
                        "dimensions" => array("all", "device", "browser"),
                        "metrics" => array("totalViews", "uniqueViews", "averageViews"),
                        "filters" => array(
                            "device" => array("value", "ios")
                        ),
                    )
                ),
                "flags" => array("nestedDimensions" => false),
                "format" => "nested",
                "comparator" => "change"
            )
        );
        $this->client->request("POST", "/api/analytics/source/view/bulkstats", array(), array(), array(), $post);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('-56.25%', $response['comparator'][0]['all']['totalViews'], $this->debug($response));
        $this->assertSame('16.67%', $response['comparator'][0]['device']['ios']['totalViews'], $this->debug($response));
        $this->assertSame('85.71%', $response['comparator'][1]['all']['totalViews'], $this->debug($response));
        $this->assertSame('100.00%', $response['comparator'][1]['browser']['chrome']['totalViews'], $this->debug($response));
    }

    public function testStatsSourceApiWithNamedConnection() {
        $this->createData();
        $post = json_encode(array(
            "dimensions" => array("all", "device"),
            "metrics" => array("totalViews", "uniqueViews", "averageViews"),
            "filters" => array(),
            "flags" => array("nestedDimensions" => false),
            "format" => "nested"
        ));
        // view_local uses a named connection
        $this->client->request("POST", "/api/analytics/source/view_local/stats", array(), array(), array(), $post);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('23.0', $response["all"]['totalViews'], $this->debug($response));
        $this->assertSame('4.0', $response["all"]['uniqueViews'], $this->debug($response));
        $this->assertSame('5.8', $response["all"]['averageViews'], $this->debug($response));
    }

    public function testDocumentsSourceApi() {
        $this->createData();
        $post = json_encode(array(
            "filters" => array()
        ));
        $this->client->request("POST", "/api/analytics/source/view/documents", array(), array(), array(), $post);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(count($response), 4, $this->debug($response));
        $this->assertSame('ios', $response[0]['device'], $this->debug($response));
        $this->assertSame('chrome', $response[0]['browser'], $this->debug($response));
        $this->assertSame(6, $response[0]['views'], $this->debug($response));
    }
}
