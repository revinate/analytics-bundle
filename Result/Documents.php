<?php

namespace Revinate\AnalyticsBundle\Result;

use Revinate\AnalyticsBundle\FilterSource\FilterSourceInterface;

class Documents extends AbstractResult {

    /**
     * @return mixed
     */
    public function getResult() {
        $documents = array();
        /** @var FilterSourceInterface[] $fieldToFilterSource */
        $fieldToFilterSource = array();
        foreach ($this->analytics->getDimensions() as $dimension) {
            if ($filterSource = $dimension->getFilterSource()) {
                $fieldToFilterSource[$dimension->getField()] = $filterSource;
            }
        }

        foreach ($this->getElasticaResultSet()->getResults() as $result) {
            $doc = $result->getData();
            $doc["_id"] = $result->getId();
            foreach ($fieldToFilterSource as $field => $filterSource) {
                if (isset($doc[$field])) {
                    // TODO: replace get() with mget()
                    $doc[$field . "_info"] = $filterSource->get($doc[$field]);
                }
            }
            $documents[] = $doc;
        }
        return $documents;
    }
}