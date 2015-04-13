<?php

namespace Revinate\AnalyticsBundle\Filter;

interface CustomFilterInterface {

    /**
     * @return string
     */
    public function getName();

    /**
     * @param mixed $value Optional value
     * @return \Elastica\Filter\AbstractFilter
     */
    public function getFilter($value = null);

    /**
     * @return mixed
     */
    public function toArray();
}