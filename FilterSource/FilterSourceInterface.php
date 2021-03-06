<?php

namespace Revinate\AnalyticsBundle\FilterSource;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interface FilterSourceInterface
 * @package Revinate\AnalyticsBundle\FilterSource
 */
interface FilterSourceInterface {

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param $name
     * @return self
     */
    public static function create(ContainerInterface $container, $name);

    /**
     * Filter Name
     * @return string
     */
    public function getReadableName();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $field
     */
    public function setField($field);

    /**
     * @return string
     */
    public function getField();

    /**
     * @return array
     */
    public function toArray();

        /**
     * @param string|int $id
     * @return array
     */
    public function get($id);

    /**
     * @param array $ids
     * @return array
     */
    public function mget(array $ids);

    /**
     * @param string $query
     * @param $page
     * @param $pageSize
     * @return array
     */
    public function getByQuery($query, $page, $pageSize);

    /**
     * @return array
     */
    public function getAll();

    /**
     * @return string
     */
    public function getNameColumn();

    /**
     * @return string
     */
    public function getIdColumn();
}