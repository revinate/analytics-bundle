<?php

namespace Revinate\AnalyticsBundle\Controller;

use Revinate\AnalyticsBundle\AnalyticsInterface;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;

class FilterController extends Controller {

    /**
     * Filter Controller
     */
    public function queryAction($source, $filter, $query, $page = 1, $pageSize = 10) {
        $container = $this->get('service_container');
        $config = $container->getParameter('revinate_analytics.config');
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }

        $sourceConfig = $config['sources'][$source];
        /** @var AnalyticsInterface $analytics */
        $analytics = new $sourceConfig['class']($this->get('service_container'));
        $analyticsFilter = null;
        foreach ($analytics->getFilterSources() as $filterInstance) {
            if ($filterInstance->getName() == $filter) {
                $analyticsFilter = $filterInstance;
                break;
            }
        }
        if (! $analyticsFilter) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }

        $results = $analyticsFilter->getByQuery($query, $page, $pageSize);
        $jsonResults = array();
        foreach ($results as $result) {
            $jsonResults[] = array(
                'id' => $result->getId(),
                'name' => $result->getName(),
            );
        }
        return new JsonResponse($jsonResults);
    }
}