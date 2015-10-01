<?php
namespace Revinate\AnalyticsBundle\Test\TestCase;

use AppKernel;
use Elastica\Query;
use Revinate\AnalyticsBundle\Service\ElasticaService;
use Revinate\AnalyticsBundle\Test\Entity\ViewAnalytics;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseTestCase extends WebTestCase {
    /** @var  AppKernel */
    protected static $kernel;
    /** @var ContainerInterface */
    protected static $container;
    /** @var string ES index prefix */
    protected static $indexPrefix;
    /** @var bool Defining if it's initialize (like some stuff we just need to run once like setting up the mysql schema, es mapping, etc) */
    private static $initialized = false;
    /** @var  \Elastica\Client */
    protected $elasticaClient;
    /** @var  \Elastica\Index */
    protected $index;
    /** @var  \Elastica\Type */
    protected $type;

    /**
     * Initialize function, which will only be run once
     */
    private static function initialize() {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        if (! self::$initialized) {
            self::$kernel = new AppKernel('test_local', true);
            self::$kernel->boot();
            self::$initialized = true;
        }
    }

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass() {
        self::initialize();
    }

    protected function setUp()
    {
        /** @var ElasticaService $elasticaService */
        $elasticaService = $this->getContainer()->get("revinate_analytics.elastica");
        $this->elasticaClient = $elasticaService->getInstance(null);
        $this->index = new \Elastica\Index($this->elasticaClient, ViewAnalytics::INDEX_NAME);
        if (! $this->index->exists()) {
            $this->index->create(array("index.number_of_replicas" => "0", "index.number_of_shards" => "1"));
            $this->type = new \Elastica\Type($this->index, ViewAnalytics::INDEX_TYPE);
            $mappingJson = json_decode(file_get_contents(__DIR__."/../data/es/mapping.json"), true);
            $mapping = new \Elastica\Type\Mapping($this->type, $mappingJson['properties']);
            $this->type->setMapping($mapping);
        } else {
            $this->type = new \Elastica\Type($this->index, ViewAnalytics::INDEX_TYPE);
        }
    }

    protected function teardown() {
        $this->index->delete();
    }

    /**
     * Get the service container
     *
     * @return ContainerInterface
     */
    public function getContainer() {
        return self::$kernel->getContainer();
    }

    /**
     * Returns a random string of given length
     * @param int   $length  length of random string
     * @return string
     */
    protected static function getRandomString($length) {
        $string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($string), 0, $length);
    }
}