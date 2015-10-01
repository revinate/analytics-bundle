<?php

namespace Revinate\AnalyticsBundle\Service;

use Revinate\AnalyticsBundle\Exception\MissingConnectionConfigException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ElasticaService {
    /** @var  \Elastica\Client */
    protected $clients;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->config = $container->getParameter('revinate_analytics.config');
    }

    /**
     * @param string $source
     * @return \Elastica\Client
     */
    public function getInstance($source = null) {
        $key = $source ? $source : "default";
        if (! isset($this->clients[$key])) {
            $connection = $this->getConnection($source);
            $this->clients[$key] = new \Elastica\Client($connection);
        }
        return $this->clients[$key];
    }

    /**
     * @param $source
     * @return mixed
     */
    protected function getConnection($source = null) {
        if (is_null($source)) {
            return $this->config["connection"];
        }
        $config = $this->config['sources'][$source];
        if (isset($config["connection"]) && ! isset($this->config["connections"][$config["connection"]])) {
            throw new MissingConnectionConfigException("Connection Config not found for connection: " . $config["connection"]);
        }
        if (isset($config["connection"]) && isset($this->config["connections"]) && isset($this->config["connections"][$config["connection"]])) {
            return $this->config["connections"][$config["connection"]];
        }
        return $this->config["connection"];
    }
}