<?php

namespace Revinate\AnalyticsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;


use Symfony\Component\Config\FileLocator;

/**
 * Class RevinateRabbitMqExtension
 * @package Revinate\RabbitMqBundle\DependencyInjection
 */
class RevinateAnalyticsExtension extends Extension
{
    /**
     * @var ContainerBuilder
     */
    private $container;
    /** @var array */
    private $config = array();

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container) {
        $this->container = $container;
        $configuration = new Configuration();
        $this->config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Set Default Host and Port
        if (! isset($this->config['connection']['host'])) {
            $this->config['connection']['host'] = '127.0.0.1';
        }
        if (! isset($this->config['connection']['port'])) {
            $this->config['connection']['port'] = 9200;
        }
        $this->container->setParameter("revinate_analytics.config", $this->config);
    }

}
