<?php

namespace Revinate\AnalyticsBundle\Dimension;

interface DimensionInterface {

    /**
     * @param $name
     * @param null $field
     * @return self
     */
    public static function create($name, $field = null);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getField();

    /**
     * @return mixed
     */
    public function toArray();
}