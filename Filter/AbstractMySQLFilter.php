<?php

namespace Revinate\AnalyticsBundle\Filter;

use Doctrine\Entity;
use Revinate\AnalyticsBundle\Filter\Result\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractMySQLFilter extends AbstractFilter implements FilterInterface {

    /**
     * @param int|string $id
     * @return Result
     */
    public function get($id) {
        $entity = $this->getRepository()->find($id);
        $results = $this->results(array($entity));
        return $results[0];
    }

    /**
     * @param string $query
     * @param $page
     * @param $pageSize
     * @return Result[]
     */
    public function getByQuery($query, $page, $pageSize) {
        $repository = $this->getRepository();
        $qb = $repository->createQueryBuilder("entity");
        $qb->select('entity');
        if ($query !== AbstractFilter::ALL) {
            $qb->where($qb->expr()->like("entity.name", ":query"))
                ->setParameter('query', '%'.$query.'%')
            ;
        }
        $qb->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize);
        $query = $qb->getQuery();
        $entities = $query->execute();
        return $this->results($entities);
    }

    /**
     * @param FilterInterface[] $entities
     * @return Result[]
     */
    protected function results($entities) {
        $results = array();
        foreach ($entities as $entity) {
            $results[] = new Result($this->getEntityId($entity), $this->getEntityName($entity));
        }
        return $results;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository() {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository($this->getModel());
    }

}