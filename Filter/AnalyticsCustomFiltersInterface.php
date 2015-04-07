<?php

namespace Revinate\AnalyticsBundle\Filter;



interface AnalyticsCustomFiltersInterface {

    /**
     * @return CustomFilterInterface[]
     */
    public function getCustomFilters();

    /**
     * @param $name
     * @return CustomFilterInterface
     */
    public function getCustomFilter($name);
}