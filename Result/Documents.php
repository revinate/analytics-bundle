<?php

namespace Revinate\AnalyticsBundle\Result;

class Documents extends AbstractResult {

    /**
     * @return mixed
     */
    public function getResult() {
        $documents = array();
        foreach ($this->getElasticaResultSet()->getResults() as $result) {
            $doc = $result->getData();
            $doc["_id"] = $result->getId();
            $documents[] = $doc;
        }
        return $documents;
    }
}