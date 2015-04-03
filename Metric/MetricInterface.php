<?php

namespace Revinate\AnalyticsBundle\Metric;

interface MetricInterface {

    /**
     * @param string $name
     * @param string $field
     * @return self
     */
    public static function create($name, $field);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getField();

    /**
     * @return string
     */
    public function getResult();

    /**
     * @return mixed
     */
    public function getDefault();

    /**
     * @return \Elastica\Filter\AbstractFilter
     */
    public function getFilter();

    /**
     * @return string
     */
    public function getNestedPath();

    /**
     * @return mixed
     */
    public function toArray();
}