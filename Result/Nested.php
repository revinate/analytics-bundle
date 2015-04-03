<?php

namespace Revinate\AnalyticsBundle\Result;

class Nested extends AbstractResult {

    /**
     * @return mixed
     */
    public function getResult() {
        return $this->getNested();
    }
}