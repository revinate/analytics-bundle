<?php

namespace Revinate\AnalyticsBundle\Routing;

use Revinate\AnalyticsBundle\FilterSource\AbstractFilterSource;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RoutingLoader extends Loader {

    /**
     * Loads a resource.
     *
     * @param mixed $resource The resource
     * @param string $type The resource type
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function load($resource, $type = null) {
        $routes = new RouteCollection();

        // Route for all Configs
        $route = new Route('/api-new/revinate/analytics/config',
            array('_controller' => 'RevinateAnalyticsBundle:Config:list'));
        $routes->add("revinate_analytics_config_list", $route);


        // Route for Config
        $route = new Route('/api-new/revinate/analytics/{source}/config',
            array('_controller' => 'RevinateAnalyticsBundle:Config:get'));
        $routes->add("revinate_analytics_config_get", $route);

        // Route for Stats
        $route = new Route('/api-new/revinate/analytics/{source}/stats',
            array('_controller' => 'RevinateAnalyticsBundle:Stats:searchStats'),
            array(), array(), '', array(), array('POST'));
        $routes->add("revinate_analytics_stats_search", $route);

        // Route for Filters
        $route = new Route('/api-new/revinate/analytics/{source}/filter/{filter}/{query}/{page}/{pageSize}',
            array('_controller' => 'RevinateAnalyticsBundle:Filter:query', "query" => AbstractFilterSource::ALL, "page" => 1, "pageSize" => 10));
        $routes->add("revinate_analytics_filter_query", $route);

        return $routes;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed $resource A resource
     * @param string $type The resource type
     *
     * @return bool    true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null) {
        return $type === 'revinate_analytics';
    }
}