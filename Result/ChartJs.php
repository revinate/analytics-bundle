<?php

namespace Revinate\AnalyticsBundle\Result;



class ChartJs extends AbstractResult {

    /**
     * @return mixed
     */
    public function getResult() {
        return $this->buildResult();
    }

    /**
     * @return mixed
     */
    protected function buildResult() {
        $tabular = new Tabular($this->getQueryBuilder(), $this->getElasticaResultSet());
        $analytics = $this->getQueryBuilder()->getAnalytics();

        $labels = array();
        $datasets = array();
        $valuesByMetric = array();
        foreach ($tabular->getResult() as $dimensionName => $metricValues) {
            $labels[] = $dimensionName;
            foreach ($this->getQueryBuilder()->getMetrics() as $metricName) {
                $metric = $analytics->getMetric($metricName);
                $valuesByMetric[$metricName][] = isset($metricValues[$metricName]) ? $metricValues[$metricName] : $metric->getDefault();
            }
        }
        foreach ($valuesByMetric as $metricName => $metricValues) {
            $datasets[] = array('label' => $metricName, 'data' => $metricValues);
        }
        return array('labels' => $labels, "datasets" => $datasets);
    }

}