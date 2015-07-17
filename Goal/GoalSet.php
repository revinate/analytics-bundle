<?php

namespace Revinate\AnalyticsBundle\Goal;

use Revinate\AnalyticsBundle\Exception\InvalidGoalFormatTypeException;
use Revinate\AnalyticsBundle\Metric\Result;
use Revinate\AnalyticsBundle\Query\QueryBuilder;
use Revinate\AnalyticsBundle\Result\ResultSet;

class GoalSet {
    /** @var Goal[]  */
    protected $goals;
    /** @var ResultSet  */
    protected $resultSet;
    /** @var array */
    protected $goalsByKey;

    /**
     * @param Goal[] $goals
     * @param ResultSet $resultSet
     */
    public function __construct($goals, ResultSet $resultSet) {
        $this->goals = $goals ? $goals : array();
        foreach ($this->goals as $goal) {
            $this->goalsByKey[$goal->getMetric()] = $goal->getValue();
        }
        $this->resultSet = $resultSet;
    }

    /**
     * @param string $format
     * @return array
     * @throws InvalidGoalFormatTypeException
     */
    public function get($format = ResultSet::TYPE_NESTED) {
        if (! in_array($format, array(ResultSet::TYPE_NESTED, ResultSet::TYPE_TABULAR, ResultSet::TYPE_FLATTENED))) {
            throw new InvalidGoalFormatTypeException("Invalid Goal Format given. Only Nested, Tabular and Flattened formats are supported");
        }
        return $this->getGoalsRecursively($this->resultSet->getResult($format));
    }

    /**
     * @param $result
     * @return array
     */
    protected function getGoalsRecursively($result) {
        $goals = array();
        foreach ($result as $key => $value) {
            if (is_array($value)) {
                $goals[$key] = $this->getGoalsRecursively($value);
            } else {
                $keyValue = $this->getKeyValue($key);
                $goalValue = isset($this->goalsByKey[$keyValue]) ? $this->goalsByKey[$keyValue] : null;
                $goals[$keyValue] = $goalValue ? $this->getFormattedValue(($value / $goalValue * 100)) : null;
            }
        }
        return $goals;
    }

    /**
     * @param $value
     * @return string
     */
    protected function getFormattedValue($value) {
        return sprintf("%.2f%%", $value);
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function getKeyValue($key) {
        $keyParts = explode(".", $key);
        return $keyParts[count($keyParts) - 1];
    }
}