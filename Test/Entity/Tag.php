<?php

namespace Revinate\AnalyticsBundle\Test\Entity;

class Tag {
    /** @var  string */
    protected $name;
    /** @var  float */
    protected $weightage;

    /**
     * @param string $name
     * @param float  $weightage
     */
    function __construct($name, $weightage)
    {
        $this->name = $name;
        $this->weightage = $weightage;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return float
     */
    public function getWeightage()
    {
        return $this->weightage;
    }

    /**
     * @param float $weightage
     */
    public function setWeightage($weightage)
    {
        $this->weightage = $weightage;
    }

    public function toArray() {
        return array(
            "name" => $this->getName(),
            "weightage" => $this->getWeightage(),
        );
    }
}