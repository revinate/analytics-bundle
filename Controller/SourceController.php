<?php

namespace Revinate\AnalyticsBundle\Controller;

use Revinate\AnalyticsBundle\BaseAnalytics;
use Revinate\AnalyticsBundle\BaseAnalyticsInterface;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SourceController extends Controller {

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
                    'uri' => $router->generate('revinate_analytics_source_get', array('source' => $key), true),
                    'method' => 'GET'
                )
            );
        }
        return new JsonResponse($data);
    }

    /**
     * Config Controller
     * @param $source
     * @return JsonResponse
     */
    public function getAction($source) {
        /** @var ContainerInterface $container */
        $container = $this->get('service_container');
        $config = $container->getParameter('revinate_analytics.config');
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        $sourceConfig = $config['sources'][$source];
        /** @var BaseAnalytics $analytics */
        $analytics = new $sourceConfig['class']($this->get('service_container'));
        $this->setContextFromRequest($analytics);
        $data = array_merge(
            $analytics->getConfig(),
            array('_links' => $this->getLinks($analytics, $source))
        );
        return new JsonResponse($data);
    }

    /**
     * @param $source
     * @return JsonResponse
     */
    public function getDimensionsAction($source) {
        // @TODO: Support pagination and search by query
        $config = $this->get('service_container')->getParameter('revinate_analytics.config');
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        $sourceConfig = $config['sources'][$source];
        /** @var BaseAnalytics $analytics */
        $analytics = new $sourceConfig['class']($this->get('service_container'));
        $this->setContextFromRequest($analytics);
        return new JsonResponse($analytics->getDimensionsArray());
    }

    /**
     * @param $source
     * @return JsonResponse
     */
    public function getMetricsAction($source) {
        // @TODO: Support pagination and search by query
        $config = $this->get('service_container')->getParameter('revinate_analytics.config');
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        $sourceConfig = $config['sources'][$source];
        /** @var BaseAnalytics $analytics */
        $analytics = new $sourceConfig['class']($this->get('service_container'));
        $this->setContextFromRequest($analytics);
        return new JsonResponse($analytics->getMetricsArray());
    }

    /**
     * @param $source
     * @return JsonResponse
     */
    public function getFilterSourcesAction($source) {
        $config = $this->get('service_container')->getParameter('revinate_analytics.config');
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        $sourceConfig = $config['sources'][$source];
        /** @var BaseAnalytics $analytics */
        $analytics = new $sourceConfig['class']($this->get('service_container'));
        $this->setContextFromRequest($analytics);
        return new JsonResponse($analytics->getFilterSourcesArray());
    }

    /**
     * @param BaseAnalyticsInterface $analytics
     */
    protected function setContextFromRequest(BaseAnalyticsInterface $analytics) {
        $request = $this->get('request_stack')->getMasterRequest();
        $params = $request->query->all();
        $analytics->setContext($params);
    }

    /**
     * @param BaseAnalyticsInterface $analytics
     * @param $source
     * @return array
     */
    protected function getLinks(BaseAnalyticsInterface $analytics, $source) {
        /** @var Router $router */
        $router = $this->container->get('router');
        $filterLinks = array();
        foreach ($analytics->getFilterSources() as $filter) {
            $filterLinks[$filter->getName()] = array(
                "all" => array(
                    'uri' => $router->generate('revinate_analytics_filter_list', array('source' => $source, 'filter' => $filter->getName()), true),
                    'method' => 'GET'
                ),
                "get" => array(
                    'uri' => $router->generate('revinate_analytics_filter_get', array('source' => $source, 'filter' => $filter->getName(), 'id' => '1'), true),
                    'method' => 'GET'
                ),
                "query" => array(
                    'uri' => $router->generate('revinate_analytics_filter_query', array('source' => $source, 'filter' => $filter->getName(), 'page' => '1', 'pageSize' => '20'), true) . "?query=query",
                    'method' => 'GET'
                ),
            );
        }
        return array(
            'stats' => array(
                'uri' => $router->generate('revinate_analytics_stats_search', array('source' => $source), true),
                'method' => 'POST'
            ),
            'documents' => array(
                'uri' => $router->generate('revinate_analytics_document_search', array('source' => $source), true),
                'method' => 'POST'
            ),
            'filterSources' => $filterLinks
        );
    }
}