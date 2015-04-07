<?php

namespace Revinate\AnalyticsBundle\Result;

class Documents extends AbstractResult {

    /**
     * @return mixed
     */
    public function getResult() {
        $documents = array();
        foreach ($this->getElasticaResultSet()->getResults() as $result) {
            $documents[] = $result->getData();
        }
        return $documents;
    }
}