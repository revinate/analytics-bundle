<?php

namespace Revinate\AnalyticsBundle\FilterSource;
use Doctrine\Entity;
use Revinate\AnalyticsBundle\FilterSource\Result\Result;
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
     * @return Result
     */
    public function get($id);

    /**
     * @param string $query
     * @param $page
     * @param $pageSize
     * @return Result[]
     */
    public function getByQuery($query, $page, $pageSize);

    /**
     * @param object $entity
     * @return string
     */
    public function getEntityName($entity);

    /**
     * @param object $entity
     * @return string
     */
    public function getEntityId($entity);

}