<?php

namespace Revinate\AnalyticsBundle\Routing;

use Revinate\AnalyticsBundle\FilterSource\AbstractFilterSource;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RoutingLoader extends Loader {
    const DEFAULT_PATH = '/api/analytics';

    /** @var  ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * Loads a resource.
     *
     * @param mixed $resource The resource
     * @param string $type The resource type
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function load($resource, $type = null) {
        $routes = new RouteCollection();
        $config = $this->container->getParameter('revinate_analytics.config');
        $basePath = isset($config['api']['path']) ? $config['api']['path'] : self::DEFAULT_PATH;
        $basePath = $basePath[strlen($basePath) - 1] == '/' ? substr($basePath, 0, strlen($basePath) - 1) : $basePath;

        // Route for all Sources
        $route = new Route($basePath . '/source',
            array('_controller' => 'RevinateAnalyticsBundle:Source:list'));
        $routes->add("revinate_analytics_source_list", $route);

        // Route for Source
        $route = new Route($basePath . '/source/{source}',
            array('_controller' => 'RevinateAnalyticsBundle:Source:get'));
        $routes->add("revinate_analytics_source_get", $route);

        // Route for Stats
        $route = new Route($basePath . '/source/{source}/stats',
            array('_controller' => 'RevinateAnalyticsBundle:Stats:searchStats'),
            array(), array(), '', array(), array('POST'));
        $routes->add("revinate_analytics_stats_search", $route);

        // Route for Filters
        $route = new Route($basePath . '/source/{source}/filter/{filter}/{query}/{page}/{pageSize}',
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