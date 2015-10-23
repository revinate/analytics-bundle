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
     * @param string|int $id
     * @return array
     */
    public function get($id);

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