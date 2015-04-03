<?php

namespace Revinate\AnalyticsBundle\Result;

class Raw extends AbstractResult {

    /**
     * @return mixed
     */
    public function getResult() {
        return $this->getRaw();
    }
}