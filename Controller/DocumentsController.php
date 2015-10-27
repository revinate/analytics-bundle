<?php

namespace Revinate\AnalyticsBundle\Controller;

use Revinate\AnalyticsBundle\Analytics;
use Revinate\AnalyticsBundle\AnalyticsInterface;
use Revinate\AnalyticsBundle\Exception\InvalidResultFormatTypeException;
use Revinate\AnalyticsBundle\Filter\AnalyticsCustomFiltersInterface;
use Revinate\AnalyticsBundle\Metric\Result;
use Revinate\AnalyticsBundle\Query\BulkQueryBuilder;
use Revinate\AnalyticsBundle\Query\QueryBuilder;
use Revinate\AnalyticsBundle\Result\ResultSet;
use Revinate\AnalyticsBundle\Service\ElasticaService;
use Revinate\AnalyticsBundle\Filter\FilterHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;

class DocumentsController extends Controller {

    /**
     * Documents Controller
     * @param $source
     * @return JsonResponse
     * @throws \Exception
     */
    public function searchAction($source) {
        $container = $this->get('service_container');
        $config = $container->getParameter('revinate_analytics.config');
        $post = json_decode($this->get('request_stack')->getMasterRequest()->getContent(), true);
        if (!isset($config['sources'][$source])) {
            return new JsonResponse(array('ok' => false), Response::HTTP_NOT_FOUND);
        }
        if (empty($post)) {
            return new JsonResponse(array('ok' => false, '_help' => StatsController::getHelp()), Response::HTTP_BAD_REQUEST);
        }
        $size = isset($post['size']) ? $post['size'] : 10;
        $offset = isset($post['offset']) ? $post['offset'] : 0;
        $sourceConfig = $config['sources'][$source];
        /** @var Analytics $analytics */
        /** @var Analytics $analytics */
        $analytics = new $sourceConfig['class']($container);
        $analytics->setContext(isset($post['context']) ? $post['context'] : array());
        /** @var ElasticaService $elasticaService */
        $elasticaService = $container->get('revinate_analytics.elastica');
        $queryBuilder = new QueryBuilder($elasticaService->getInstance($source), $analytics);
        $queryBuilder->setSize($size)
            ->setOffset($offset);
        if (isset($post['sort'])) {
            $queryBuilder->setSort($post['sort']);
        }
        if (! empty($post['filters'])) {
            $queryBuilder->setFilter(StatsController::getFilters($analytics, $post['filters']));
        }
        $response = array("results" => array());
        $status = Response::HTTP_OK;
        try {
            $response["results"] = $queryBuilder->execute()->getDocuments();
        } catch (\Exception $e) {
            error_log(__METHOD__. " : Error getting documents: " . $e->getMessage());
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response = array("ok" => false, "_help" => $e->getMessage());
        }
        return new JsonResponse($response, $status);
    }
}