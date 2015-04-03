<?php

namespace Revinate\AnalyticsBundle\Result;

class Tabular extends AbstractResult {

    /**
     * @return mixed
     */
    public function getResult() {
        return $this->buildResult($this->getNested());
    }

    /**
     * @param $data
     * @param $tabular
     * @param $prefixKey
     * @return mixed
     */
    protected function buildResult($data, $tabular = array(), $prefixKey = '') {
        foreach ($data as $key => $values) {
                if (! $this->isArrayOfArray($values) && is_array($values)) {
                    $tabular[$this->getJoinedKey(array($prefixKey, $key))] = $values;
                } else {
                    $tabular = array_merge(
                        $tabular,
                        $this->buildResult($values, array(), $this->getJoinedKey(array($prefixKey, $key)))
                    );
                }
        }
        return $tabular;
    }

}