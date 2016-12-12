<?php

namespace Revinate\AnalyticsBundle\Result;

class Nested extends AbstractResult {

    /**
     * @return array
     */
    public function getResult() {
        return $this->getNested();
    }

    /**
     * @return array
     */
    public function getResultRaw() {
        return $this->getNestedRaw();
    }
}