<?php
namespace Revinate\AnalyticsBundle\Test\TestCase\Controller;

use Revinate\AnalyticsBundle\Test\TestCase\BaseTestCase;
use Symfony\Component\Routing\Router;

class BaseControllerTestCase extends BaseTestCase
{
    protected function debug($results) {
        return "Results: " . print_r($results, true);
    }

    public function testRoutes()
    {
        /** @var Router $router */
        $router = $this->getContainer()->get('router');
        $routesByPath = array();
        foreach($router->getRouteCollection()->all() as $route) {
            $routesByPath[$route->getPath()] =  true;
        }
        $this->assertTrue(isset($routesByPath['/api/analytics/source']), $this->debug($routesByPath));
        $this->assertTrue(isset($routesByPath['/api/analytics/source/{source}']), $this->debug($routesByPath));
        $this->assertTrue(isset($routesByPath['/api/analytics/source/{source}/stats']), $this->debug($routesByPath));
        $this->assertTrue(isset($routesByPath['/api/analytics/source/{source}/filter/{filter}/query/{page}/{pageSize}']), $this->debug($routesByPath));
        $this->assertTrue(isset($routesByPath['/api/analytics/source/{source}/filter/{filter}/{id}']), $this->debug($routesByPath));
        $this->assertTrue(isset($routesByPath['/api/analytics/source/{source}/filter/{filter}']), $this->debug($routesByPath));
    }


    public function testConfig()
    {
        $config = $this->getContainer()->getParameter('revinate_analytics.config');
        $this->assertSame(3, count($config['sources']), $this->debug($config));
        $this->assertTrue(isset($config['sources']['view']), $this->debug($config));
        $this->assertSame('Revinate\AnalyticsBundle\Test\Entity\ViewAnalytics', $config['sources']['view']['class'], $this->debug($config));
    }
}
