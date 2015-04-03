<?php

namespace Revinate\AnalyticsBundle\Filter;
use Doctrine\Entity;
use Revinate\AnalyticsBundle\Filter\Result\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interface FilterInterface
 * @package Revinate\AnalyticsBundle\Filter
 */
interface FilterInterface {

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param $field
     * @return self
     */
    public static function create(ContainerInterface $container, $field);

    /**
     * Filter Name
     * @return string
     */
    public function getField();

    /**
     * Filter Name
     * @return string
     */
    public function getName();

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
     * @return string
     */
    public function getModel();

    /**
     * @param Entity $entity
     * @return string
     */
    public function getEntityName($entity);

    /**
     * @param Entity $entity
     * @return string
     */
    public function getEntityId($entity);

    /**
     * @return mixed
     */
    public function toArray();
}