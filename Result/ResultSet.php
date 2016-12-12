<?php

namespace Revinate\AnalyticsBundle\Result;


use Revinate\AnalyticsBundle\Exception\InvalidResultFormatTypeException;
use Revinate\AnalyticsBundle\Query\QueryBuilder;

class ResultSet {

    const TYPE_NESTED = 'nested';
    const TYPE_FLATTENED = 'flattened';
    const TYPE_TABULAR = 'tabular';
    const TYPE_RAW = 'raw';
    const TYPE_GOOGLE_DATA_TABLE = 'google_data_table';
    const TYPE_CHART_JS = 'chartjs';
    const TYPE_DOCUMENTS = 'documents';

    /** @var array */
    protected $data;
    /** @var \Revinate\AnalyticsBundle\Query\QueryBuilder  */
    protected $queryBuilder;
    /** @var \Elastica\ResultSet  */
    protected $elasticaResultSet;

    /**
     * @param QueryBuilder $queryBuilder
     * @param \Elastica\ResultSet $elasticaResultSet
     */
    public function __construct(QueryBuilder $queryBuilder, \Elastica\ResultSet $elasticaResultSet) {
        $this->queryBuilder = $queryBuilder;
        $this->elasticaResultSet = $elasticaResultSet;
        $this->data = $elasticaResultSet->getAggregations();
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder() {
        return $this->queryBuilder;
    }

    /**
     * @return array|mixed
     */
    public function getTabular() {
        $resultType = new Tabular($this->queryBuilder, $this->elasticaResultSet);
        return $resultType->getResult();
    }

    /**
     * @return array
     */
    public function getFlattened() {
        $resultType = new Flattened($this->queryBuilder, $this->elasticaResultSet);
        return $resultType->getResult();
    }

    /**
     * @return array|mixed
     */
    public function getNested() {
        $resultType = new Nested($this->queryBuilder, $this->elasticaResultSet);
        return $resultType->getResult();
    }

    /**
     * @return array|mixed
     */
    public function getNestedRaw() {
        $resultType = new Nested($this->queryBuilder, $this->elasticaResultSet);
        return $resultType->getResultRaw();
    }

    /**
     * @return array
     */
    public function getRaw() {
        $resultType = new Raw($this->queryBuilder, $this->elasticaResultSet);
        return $resultType->getResult();
    }

    /**
     * @return mixed
     */
    public function getGoogleDataTable() {
        $resultType = new GoogleDataTable($this->queryBuilder, $this->elasticaResultSet);
        return $resultType->getResult();
    }

    /**
     * @return mixed
     */
    public function getChartJs() {
        $resultType = new ChartJs($this->queryBuilder, $this->elasticaResultSet);
        return $resultType->getResult();
    }

    /**
     * @return mixed
     */
    public function getDocuments() {
        $resultType = new Documents($this->queryBuilder, $this->elasticaResultSet);
        return $resultType->getResult();
    }

    /**
     * @param $format
     * @return array|mixed
     * @throws InvalidResultFormatTypeException
     */
    public function getResult($format) {
        switch ($format) {
            case self::TYPE_NESTED:
                return $this->getNested();
            case self::TYPE_FLATTENED:
                return $this->getFlattened();
            case self::TYPE_TABULAR:
                return $this->getTabular();
            case self::TYPE_RAW:
                return $this->getRaw();
            case self::TYPE_GOOGLE_DATA_TABLE:
                return $this->getGoogleDataTable();
            case self::TYPE_CHART_JS:
                return $this->getChartJs();
            case self::TYPE_DOCUMENTS:
                return $this->getDocuments();
            default:
                throw new InvalidResultFormatTypeException("Invalid Result Format: $format");
        }
    }
}