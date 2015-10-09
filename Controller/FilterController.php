<?php

namespace Revinate\AnalyticsBundle\Controller;

use Revinate\AnalyticsBundle\AnalyticsInterface;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;

class FilterController extends Controller {

    /**
     * Filter Controller
     * @param $source
     * @param $filter
     * @param int $page
     * @param int $pageSize
     * @return JsonResponse
     */
    public function queryAction($source, $filter, $page = 1, $pageSize = 10) {
        /** @var ContainerInterface $container */
        $container = $this->get('service_container');
        $config = $container->getParameter('revinate_analytics.config');
        $query = $this->get('request_stack')->getMasterRequest()->query->get("query");
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        if (empty($query)) {
            return new JsonResponse(array('error' => 'Missing Query Param'), Response::HTTP_BAD_REQUEST);
        }

        $analyticsFilter = $this->getAnalyticsFilter($config['sources'][$source], $filter);
        if (! $analyticsFilter) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        $results = $analyticsFilter->getByQuery($query, $page, $pageSize);
        return new JsonResponse($results);
    }

    public function getAction($source, $filter, $id) {
        /** @var ContainerInterface $container */
        $container = $this->get('service_container');
        $config = $container->getParameter('revinate_analytics.config');
        if (!isset($config['sources'][$source]) || empty($id)) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        $analyticsFilter = $this->getAnalyticsFilter($config['sources'][$source], $filter);
        if (! $analyticsFilter) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        $result = $analyticsFilter->get($id);
        return new JsonResponse($result);
    }

    /**
     * @param $sourceConfig
     * @param $filter
     * @return null|\Revinate\AnalyticsBundle\FilterSource\AbstractFilterSource
     */
    protected function getAnalyticsFilter($sourceConfig, $filter) {
        /** @var AnalyticsInterface $analytics */
        $analytics = new $sourceConfig['class']($this->get('service_container'));
        $analyticsFilter = null;
        foreach ($analytics->getFilterSources() as $filterInstance) {
            if ($filterInstance->getName() == $filter) {
                $analyticsFilter = $filterInstance;
                break;
            }
        }
        return $analyticsFilter;
    }
}