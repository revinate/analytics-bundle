<?php

namespace Revinate\AnalyticsBundle\FilterSource\Result;

class Result {

    /** @var  int */
    protected $id;

    /** @var  string */
    protected $name;
    /**
     * @param $id
     * @param $name
     */
    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}