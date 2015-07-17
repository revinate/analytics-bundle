<?php

namespace Revinate\AnalyticsBundle\FilterSource;

use Revinate\AnalyticsBundle\FilterSource\Result\Result;

abstract class AbstractMySQLFilterSource extends AbstractFilterSource implements FilterSourceInterface {

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
        if ($query !== AbstractFilterSource::ALL) {
            $qb->where($qb->expr()->like("entity." . $this->getNameColumn(), ":query"))
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
     * @param FilterSourceInterface[] $entities
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
     * @return string
     */
    abstract public function getModel();

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository() {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository($this->getModel());
    }

}