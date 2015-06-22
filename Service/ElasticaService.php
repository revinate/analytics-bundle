<?php

namespace Revinate\AnalyticsBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ElasticaService {
    /** @var  \Elastica\Client */
    protected $client;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->config = $container->getParameter('revinate_analytics.config');
    }

    /**
     * @return \Elastica\Client
     */
    public function getInstance() {
        if (! $this->client) {
            $this->client = new \Elastica\Client($this->config['connection']);
        }
        return $this->client;
    }
}