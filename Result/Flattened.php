<?php

namespace Revinate\AnalyticsBundle\Result;

class Flattened extends AbstractResult {

    /**
     * @return mixed
     */
    public function getResult() {
        return $this->buildResult($this->getNested());
    }

    /**
     * @param $data
     * @param $flattened
     * @param $prefixKey
     * @return array
     */
    public function buildResult($data, $flattened = array(), $prefixKey = '') {
        foreach ($data as $key => $values) {
            if ($this->isArrayOfArray($values)) {
                $flattened = array_merge($flattened, $this->buildResult($values, $flattened, $this->getJoinedKey(array($prefixKey, $key))));
            } elseif (is_array($values)) {
                foreach ($values as $subKey => $value) {
                    $flattened[$this->getJoinedKey(array($prefixKey, $key, $subKey))] = $value;
                }
            } else {
                $flattened[$this->getJoinedKey(array($prefixKey, $key))] = $values;
            }
        }
        return $flattened;
    }
}