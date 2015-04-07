<?php

namespace Revinate\AnalyticsBundle\Filter;

interface CustomFilterInterface {

    /**
     * @return string
     */
    public function getName();

    /**
     * @return \Elastica\Filter\AbstractFilter
     */
    public function getFilter();

    /**
     * @return mixed
     */
    public function toArray();
}