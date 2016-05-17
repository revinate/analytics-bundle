<?php
/**
 * Created by PhpStorm.
 * User: vinay
 * Date: 5/12/16
 * Time: 4:06 PM
 */

namespace Revinate\AnalyticsBundle;


interface AnalyticsViewInterface {
    /**
     * @return array
     */
    public function getDimensionsArray();

    /**
     * @return array
     */
    public function getMetricsArray();

    /**
     * @return array
     */
    public function getFilterSourcesArray();

    /**
     * @return array
     */
    public function getCustomFiltersArray();
}