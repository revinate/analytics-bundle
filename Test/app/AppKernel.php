<?php

use Revinate\AnalyticsBundle\RevinateAnalyticsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel {

    /**
     * @see Kernel::__construct()
     * @param string $environment
     * @param bool $debug
     */
    public function __construct($environment, $debug) {
        date_default_timezone_set('UTC');
        parent::__construct($environment, $debug);
    }

    /**
     * Returns an array of bundles to register.
     *
     * @return BundleInterface[] An array of bundle instances.
     *
     * @api
     */
    public function registerBundles()
    {
        return array(
            new FrameworkBundle(),
            new RevinateAnalyticsBundle()
        );
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     *
     * @api
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . "/config.yml");
    }
}