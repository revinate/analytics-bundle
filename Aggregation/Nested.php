<?php

namespace Revinate\AnalyticsBundle\Aggregation;

class Nested extends \Elastica\Aggregation\Nested {
    /** @var \Elastica\Aggregation\AbstractAggregation */
    protected $subAggregation;

    /**
     * @param \Elastica\Aggregation\AbstractAggregation $aggregation
     */
    public function addSubAggregation(\Elastica\Aggregation\AbstractAggregation $aggregation) {
        $this->subAggregation = $aggregation;
    }

    /**
     * @return \Elastica\Aggregation\AbstractAggregation
     */
    public function getSubAggregation() {
        return $this->subAggregation;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        parent::addAggregation($this->getSubAggregation());

        $array = parent::toArray();
        if (array_key_exists('global_aggregation', $array)) {
            // compensate for class name GlobalAggregation
            $array = array('global' => new \stdClass);
        }
        if (sizeof($this->_aggs)) {
            $array['aggs'] = $this->_aggs;
        }
        return $array;
    }
}