<?php

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Psr\Log\LoggerInterface;

/**
 * appTestUrlGenerator
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appTestUrlGenerator extends Symfony\Component\Routing\Generator\UrlGenerator
{
    private static $declaredRoutes = array(
        'revinate_analytics_source_list' => array (  0 =>   array (  ),  1 =>   array (    '_controller' => 'Revinate\\AnalyticsBundle\\Controller\\SourceController::listAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/api/analytics/source',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'revinate_analytics_source_get' => array (  0 =>   array (    0 => 'source',  ),  1 =>   array (    '_controller' => 'Revinate\\AnalyticsBundle\\Controller\\SourceController::getAction',  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'source',    ),    1 =>     array (      0 => 'text',      1 => '/api/analytics/source',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'revinate_analytics_stats_search' => array (  0 =>   array (    0 => 'source',  ),  1 =>   array (    '_controller' => 'Revinate\\AnalyticsBundle\\Controller\\StatsController::searchStatsAction',  ),  2 =>   array (    '_method' => 'POST',  ),  3 =>   array (    0 =>     array (      0 => 'text',      1 => '/stats',    ),    1 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'source',    ),    2 =>     array (      0 => 'text',      1 => '/api/analytics/source',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
        'revinate_analytics_filter_query' => array (  0 =>   array (    0 => 'source',    1 => 'filter',    2 => 'query',    3 => 'page',    4 => 'pageSize',  ),  1 =>   array (    '_controller' => 'Revinate\\AnalyticsBundle\\Controller\\FilterController::queryAction',    'query' => '_all',    'page' => 1,    'pageSize' => 10,  ),  2 =>   array (  ),  3 =>   array (    0 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'pageSize',    ),    1 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'page',    ),    2 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'query',    ),    3 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'filter',    ),    4 =>     array (      0 => 'text',      1 => '/filter',    ),    5 =>     array (      0 => 'variable',      1 => '/',      2 => '[^/]++',      3 => 'source',    ),    6 =>     array (      0 => 'text',      1 => '/api/analytics/source',    ),  ),  4 =>   array (  ),  5 =>   array (  ),),
    );

    /**
     * Constructor.
     */
    public function __construct(RequestContext $context, LoggerInterface $logger = null)
    {
        $this->context = $context;
        $this->logger = $logger;
    }

    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        if (!isset(self::$declaredRoutes[$name])) {
            throw new RouteNotFoundException(sprintf('Unable to generate a URL for the named route "%s" as such route does not exist.', $name));
        }

        list($variables, $defaults, $requirements, $tokens, $hostTokens, $requiredSchemes) = self::$declaredRoutes[$name];

        return $this->doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);
    }
}
