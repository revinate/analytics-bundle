<?php

namespace Revinate\AnalyticsBundle\Controller;

use Revinate\AnalyticsBundle\BaseAnalyticsInterface;


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

    /**
     * @param $source
     * @param $filter
     * @param $id
     * @return JsonResponse
     */
    public function getAction($source, $filter, $id) {
        $idsArray = array_map("trim", explode(",", $id));
        /** @var ContainerInterface $container */
        $container = $this->get('service_container');
        $config = $container->getParameter('revinate_analytics.config');
        if (!isset($config['sources'][$source]) || empty($idsArray)) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        $analyticsFilter = $this->getAnalyticsFilter($config['sources'][$source], $filter);
        if (! $analyticsFilter) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        if (count($idsArray) == 1) {
            $result = $analyticsFilter->get($idsArray[0]);
        } else {
            $result = $analyticsFilter->mget(array_unique($idsArray));
        }
        return new JsonResponse($result);
    }

    /**
     * @param $source
     * @param $filter
     * @return JsonResponse
     */
    public function listAction($source, $filter) {
        /** @var ContainerInterface $container */
        $container = $this->get('service_container');
        $config = $container->getParameter('revinate_analytics.config');
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        $analyticsFilter = $this->getAnalyticsFilter($config['sources'][$source], $filter);
        if (! $analyticsFilter) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        $result = $analyticsFilter->getAll();
        return new JsonResponse($result);
    }

    /**
     * @param $sourceConfig
     * @param $filter
     * @return null|\Revinate\AnalyticsBundle\FilterSource\AbstractFilterSource
     */
    protected function getAnalyticsFilter($sourceConfig, $filter) {
        /** @var BaseAnalyticsInterface $analytics */
        $analytics = new $sourceConfig['class']($this->get('service_container'));
        $analyticsFilter = null;
        return $analytics->getFilterSource($filter);
    }
}