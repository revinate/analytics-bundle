<?php

namespace Revinate\AnalyticsBundle\FilterSource;

use Doctrine\ORM\Query;

abstract class AbstractMySQLFilterSource extends AbstractFilterSource implements FilterSourceInterface {

    /**
     * @param int|string $id
     * @return array
     */
    public function get($id) {
        $repository = $this->getRepository();
        $qb = $repository->createQueryBuilder("entity");
        $qb->select('entity')->where("id = :id")->setParameter("id", $id);
        $query = $qb->getQuery();
        $entity = $query->getSingleResult(Query::HYDRATE_ARRAY);
        return $entity;
    }

    /**
     * @param string $query
     * @param $page
     * @param $pageSize
     * @return array
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
        return $query->execute(null, Query::HYDRATE_ARRAY);
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