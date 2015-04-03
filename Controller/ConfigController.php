<?php

namespace Revinate\AnalyticsBundle\Controller;

use Revinate\AnalyticsBundle\AnalyticsInterface;
use Revinate\AnalyticsBundle\Filter\AbstractFilter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigController extends Controller {

    /**
     * Config List Controller
     */
    public function listAction() {
        $config = $this->get('service_container')->getParameter('revinate_analytics.config');
        $router = $this->container->get('router');

        $data = array();
        foreach ($config['sources'] as $key => $sourceConfig) {
            $data[$key] = array(
                'name' => $key,
                '_link' => array(
                    'uri' => $router->generate('revinate_analytics_config_get', array('source' => $key), true),
                    'method' => 'GET'
                )
            );
        }
        return new JsonResponse($data);
    }

    /**
     * Config Controller
     */
    public function getAction($source) {
        $config = $this->get('service_container')->getParameter('revinate_analytics.config');
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        $sourceConfig = $config['sources'][$source];
        /** @var AnalyticsInterface $analytics */
        $analytics = new $sourceConfig['class']($this->get('service_container'));
        $data = array_merge(
            $analytics->getConfig(),
            array('_links' => $this->getLinks($analytics, $source))
        );
        return new JsonResponse($data);
    }

    /**
     * @param AnalyticsInterface $analytics
     * @param $source
     * @return array
     */
    protected function getLinks(AnalyticsInterface $analytics, $source) {
        $router = $this->container->get('router');
        $filterLinks = array();
        foreach ($analytics->getFilters() as $filter) {
            $filterLinks[$filter->getField()] = array(
                'uri' => $router->generate('revinate_analytics_filter_query', array('source' => $source, 'filter' => $filter->getName(), 'query' => 'query', 'page' => '1', 'pageSize' => '20'), true),
                'method' => 'get'
            );
        }
        return array(
            'stats' => array(
                'uri' => $router->generate('revinate_analytics_stats_search', array('source' => $source), true),
                'method' => 'post'
            ),
            'filters' => $filterLinks
        );
    }
}