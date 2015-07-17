<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * appTestUrlMatcher.
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appTestUrlMatcher extends Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = rawurldecode($pathinfo);
        $context = $this->context;
        $request = $this->request;

        if (0 === strpos($pathinfo, '/api/analytics/source')) {
            // revinate_analytics_source_list
            if ($pathinfo === '/api/analytics/source') {
                return array (  '_controller' => 'Revinate\\AnalyticsBundle\\Controller\\SourceController::listAction',  '_route' => 'revinate_analytics_source_list',);
            }

            // revinate_analytics_source_get
            if (preg_match('#^/api/analytics/source/(?P<source>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => 'revinate_analytics_source_get')), array (  '_controller' => 'Revinate\\AnalyticsBundle\\Controller\\SourceController::getAction',));
            }

            // revinate_analytics_stats_search
            if (preg_match('#^/api/analytics/source/(?P<source>[^/]++)/stats$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_revinate_analytics_stats_search;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'revinate_analytics_stats_search')), array (  '_controller' => 'Revinate\\AnalyticsBundle\\Controller\\StatsController::searchStatsAction',));
            }
            not_revinate_analytics_stats_search:

            // revinate_analytics_bulk_stats_search
            if (preg_match('#^/api/analytics/source/(?P<source>[^/]++)/bulkstats$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_revinate_analytics_bulk_stats_search;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'revinate_analytics_bulk_stats_search')), array (  '_controller' => 'Revinate\\AnalyticsBundle\\Controller\\StatsController::bulkSearchStatsAction',));
            }
            not_revinate_analytics_bulk_stats_search:

            // revinate_analytics_filter_query
            if (preg_match('#^/api/analytics/source/(?P<source>[^/]++)/filter/(?P<filter>[^/]++)(?:/(?P<query>[^/]++)(?:/(?P<page>[^/]++)(?:/(?P<pageSize>[^/]++))?)?)?$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => 'revinate_analytics_filter_query')), array (  '_controller' => 'Revinate\\AnalyticsBundle\\Controller\\FilterController::queryAction',  'query' => '_all',  'page' => 1,  'pageSize' => 10,));
            }

        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
