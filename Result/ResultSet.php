<?php

namespace Revinate\AnalyticsBundle\Result;

use Revinate\AnalyticsBundle\Aggregation\AllAggregation;
use Revinate\AnalyticsBundle\Metric\ProcessedMetric;
use Revinate\AnalyticsBundle\Query\QueryBuilder;

class ResultSet {

    const TYPE_NESTED = 'nested';
    const TYPE_FLATTENED = 'flattened';
    const TYPE_TABULAR = 'tabular';
    const TYPE_RAW = 'raw';
    const TYPE_GOOGLE_DATA_TABLE = 'google_data_table';
    const TYPE_CHART_JS = 'chartjs';

    /** @var array */
    protected $data;
    /** @var \Revinate\AnalyticsBundle\Query\QueryBuilder  */
    protected $queryBuilder;

    /**
     * @param QueryBuilder $queryBuilder
     * @param $data
     */
    public function __construct(QueryBuilder $queryBuilder, $data) {
        $this->queryBuilder = $queryBuilder;
        $this->data = $data;
    }

    /**
     * @return array|mixed
     */
    public function getTabular() {
        $resultType = new Tabular($this->queryBuilder, $this->data);
        return $resultType->getResult();
    }

    /**
     * @return array
     */
    public function getFlattened() {
        $resultType = new Flattened($this->queryBuilder, $this->data);
        return $resultType->getResult();
    }

    /**
     * @return array|mixed
     */
    public function getNested() {
        $resultType = new Nested($this->queryBuilder, $this->data);
        return $resultType->getResult();
    }

    /**
     * @return array
     */
    public function getRaw() {
        $resultType = new Raw($this->queryBuilder, $this->data);
        return $resultType->getResult();
    }

    /**
     * @return mixed
     */
    public function getGoogleDataTable() {
        $resultType = new GoogleDataTable($this->queryBuilder, $this->data);
        return $resultType->getResult();
    }

    /**
     * @return mixed
     */
    public function getChartJs() {
        $resultType = new ChartJs($this->queryBuilder, $this->data);
        return $resultType->getResult();
    }

    /**
     * @param $format
     * @return array|mixed
     */
    public function getResult($format) {
        switch ($format) {
            case self::TYPE_NESTED:
                return $this->getNested();
                break;
            case self::TYPE_FLATTENED:
                return $this->getFlattened();
                break;
            case self::TYPE_TABULAR:
                return $this->getTabular();
                break;
            case self::TYPE_RAW:
                return $this->getRaw();
                break;
            case self::TYPE_GOOGLE_DATA_TABLE:
                return $this->getGoogleDataTable();
                break;
            case self::TYPE_CHART_JS:
                return $this->getChartJs();
                break;
            default:
                return $this->getNested();
        }
    }
}