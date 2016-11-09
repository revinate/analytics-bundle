<?php
/**
 * Created by PhpStorm.
 * User: vinay
 * Date: 5/18/16
 * Time: 4:30 PM
 */

namespace Revinate\AnalyticsBundle\Test\TestCase\Controller;


use Revinate\AnalyticsBundle\Test\Elastica\DocumentHelper;
use Revinate\AnalyticsBundle\Test\TestCase\BaseTestCase;
use Symfony\Component\BrowserKit\Tests\TestClient;


class SourceApiControllerTest extends BaseTestCase {
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

    public function testListSourceApiWithParams() {
        $ctx = json_encode(array("browser" => 'firefox'));
        $this->client->request("GET", "/api/analytics/source/view", array("context" => $ctx));
        $content = $this->client->getResponse()->getContent();
        $this->assertContains('Total Views For firefox', $content);
    }

    public function testListDimensionApi() {
        $this->client->request("GET", "/api/analytics/source/view/dimension");
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(count($response) > 0, $this->debug($response));
        $this->assertArrayHasKey("name", $response[0], $this->debug($response));
        $this->assertArrayHasKey("readableName", $response[0], $this->debug($response));
        $this->assertArrayHasKey("type", $response[0], $this->debug($response));
        $this->assertArrayHasKey("attributes", $response[0], $this->debug($response));
        $this->assertArrayHasKey("filterSource", $response[0], $this->debug($response));
        $this->assertArrayHasKey("size", $response[0], $this->debug($response));
    }

    public function testListDimensionApiPagination() {
        $this->client->request("GET", "/api/analytics/source/view/dimension?page=1&size=4");
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(count($response) == 4, $this->debug($response));
    }

    public function testListMetricApi() {
        $this->client->request("GET", "/api/analytics/source/view/metric");
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(count($response) > 0, $this->debug($response));
        $this->assertArrayHasKey("name", $response[0], $this->debug($response));
        $this->assertArrayHasKey("readableName", $response[0], $this->debug($response));
        $this->assertArrayHasKey("type", $response[0], $this->debug($response));
        $this->assertArrayHasKey("attributes", $response[0], $this->debug($response));
        $this->assertArrayHasKey("prefix", $response[0], $this->debug($response));
        $this->assertArrayHasKey("postfix", $response[0], $this->debug($response));
        $this->assertArrayHasKey("precision", $response[0], $this->debug($response));
    }

    public function testListMetricApiPagination() {
        $this->client->request("GET", "/api/analytics/source/view/dimension?page=1&size=4");
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(count($response) == 4, $this->debug($response));
    }

    public function testListMetricApiSearch() {
        $this->client->request("GET", "/api/analytics/source/dynamic_view/metric?query=total");
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(count($response) == 1, $this->debug($response));
        $this->assertSame($response[0]["name"], "totalViews", $this->debug($response));

        $this->client->request("GET", "/api/analytics/source/view/metric?query=total");
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(count($response) == 4, $this->debug($response));
        $this->assertSame($response[0]["name"], "totalViews", $this->debug($response));
    }

    public function testListDimensionApiSearch() {
        $this->client->request("GET", "/api/analytics/source/dynamic_view/dimension?query=site");
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(count($response) == 1, $this->debug($response));
        $this->assertSame($response[0]["name"], "site", $this->debug($response));

        $this->client->request("GET", "/api/analytics/source/view/dimension?query=site");
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(count($response) == 3, $this->debug($response));
        $this->assertSame($response[0]["name"], "site", $this->debug($response));
    }


    public function testListFilterSourceApi() {
        $this->client->request("GET", "/api/analytics/source/view/filter_source");
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(count($response) > 0, $this->debug($response));
        $this->assertArrayHasKey("name", $response[0], $this->debug($response));
        $this->assertArrayHasKey("field", $response[0], $this->debug($response));
        $this->assertArrayHasKey("type", $response[0], $this->debug($response));
        $this->assertArrayHasKey("key", $response[0], $this->debug($response));
    }

    public function testListFilterSourceApiPagination() {
        $this->client->request("GET", "/api/analytics/source/view/filter_source?page=1&size=1");
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(count($response) == 1, $this->debug($response));
    }
}