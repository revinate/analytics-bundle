<?php

namespace Revinate\AnalyticsBundle\FilterSource;

use Revinate\AnalyticsBundle\Lib\DateHelper;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DateFilterSource
 * @package Revinate\DataSourceBundle\FilterSources
 */
abstract class AbstractDateFilterSource extends AbstractFilterSource {

    /**
     * @var array
     */
    protected static $periodCodes = array(
        'lw',
        'mtd',
        'lm',
        'l3m',
        'l3mtd',
        'ytd',
        'ly',
        'wtd',
        '3da',
        '3wa',
        '3ma',
        'lq',
        '3qa',
        '1ya',
        'lmp',
        'qtd',
        'l3d',
    );

    /**
     * @var string
     */
    protected $scale = "day";


    public function __construct(ContainerInterface $container, $name, $scale) {
        parent::__construct($container, $name);
        $this->scale = $scale;
        return $this;
    }

    /**
     * @return string
     */
    public function getReadableName() {
        return "Date";
    }

    public function get($id) {
        try {
            $periodInfo = DateHelper::getPeriodInfo($id);
            $periodDimension = array();
            $periodDimension['id'] = $id;
            $periodDimension['name'] = $periodInfo['short_description'];
            $periodDimension['period'] = $periodInfo['period'];
            $periodDimension['buckets'] = DateHelper::getIntervalTimestampsForES(array($periodInfo['period'][0], $periodInfo['period'][2]), $this->scale);
            return $periodDimension;
        } catch (Exception $e) {
            return null;
        }
    }

    public function mget(array $ids) {
        $periodDimensions = array();
        foreach ($ids as $id) {
            $p = $this->get($id);
            if (!is_null($p)) {
                $periodDimensions[] = $p;
            }
        }
        return $periodDimensions;
    }

    public function getByQuery($query, $page, $pageSize) {
        if (empty($query)) {
            return array();
        }
        $matches = array();
        foreach (self::$periodCodes as $periodCode) {
            if (strpos($periodCode, $query) !== false) {
                $matches[] = $periodCode;
            }
        }
        return $this->mget($matches);
    }

    public function getAll() {
        return $this->mget(self::$periodCodes);
    }

    public function getNameColumn() {
        return 'id';
    }

    public function getIdColumn() {
        return 'name';
    }

}