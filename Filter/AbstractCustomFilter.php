<?php

namespace Revinate\AnalyticsBundle\Filter;


abstract class AbstractCustomFilter implements CustomFilterInterface {

    /**
     * @return mixed
     */
    public function toArray() {
        return array(
            'name' => $this->getName()
        );
    }
}