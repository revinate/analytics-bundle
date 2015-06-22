<?php
namespace Revinate\AnalyticsBundle\Test\TestCase\Controller;

use Revinate\AnalyticsBundle\Test\Elastica\DocumentHelper;
use Revinate\AnalyticsBundle\Test\TestCase\BaseTestCase;
use Revinate\AnalyticsBundle\Test\TestCase\BaseWebTestCase;
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
        $docHelper->createView("chrome", "ios", "-2 month", 6)
            ->createView("opera", "ios", "-3 month", 5)
            ->createView("opera", "ios", "-1 week", 2)
            ->createView("chrome", "android", "+0 day", 10)
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
        $this->assertSame(23, $response["all"]['totalViews'], $this->debug($response));
        $this->assertSame(4, $response["all"]['uniqueViews'], $this->debug($response));
        $this->assertSame(5.75, $response["all"]['averageViews'], $this->debug($response));
    }
}
