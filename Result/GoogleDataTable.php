<?php

namespace Revinate\AnalyticsBundle\Result;

use Revinate\AnalyticsBundle\Service\GoogleDataTableService;

class GoogleDataTable extends AbstractResult {

    /** @var GoogleDataTableService */
    protected $service;

    /**
     * @return mixed
     */
    public function getResult() {
        $this->service = new GoogleDataTableService();
        return $this->buildResult();
    }

    /**
     * @return mixed
     */
    protected function buildResult() {
        $tabular = new Tabular($this->getQueryBuilder(), $this->getRaw());
        $analytics = $this->getQueryBuilder()->getAnalytics();
        $this->service->addColumn('Label', GoogleDataTableService::TYPE_STRING);
        foreach ($this->getQueryBuilder()->getMetrics() as $metricName) {
            $metric = $analytics->getMetric($metricName);
            $this->service->addColumn($metric->getName(), GoogleDataTableService::TYPE_NUMBER);
        }
        foreach ($tabular->getResult() as $key => $metricValues) {
            $values = array($key);
            foreach ($this->getQueryBuilder()->getMetrics() as $metricName) {
                $values[] = isset($metricValues[$metricName]) ? $metricValues[$metricName] : 'default';
            }
            $this->service->addRow($values);
        }
        return $this->service->getDataTableObject();
    }

}