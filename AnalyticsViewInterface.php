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
     * @param $page
     * @param $size
     * @return array
     */
    public function getDimensionsArray($page, $size);

    /**
     * @param $page
     * @param $size
     * @return array
     */
    public function getMetricsArray($page, $size);

    /**
     * @return array
     */
    public function getFilterSourcesArray();

    /**
     * @return array
     */
    public function getCustomFiltersArray();
}