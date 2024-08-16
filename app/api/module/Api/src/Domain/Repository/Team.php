<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\User\Team as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class Team extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch by name
     *
     * @param string $name
     *
     * @return array
     */
    public function fetchByName($name)
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.name', ':name'))
            ->setParameter('name', $name);

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch team by Id with printers
     *
     * @param int ID
     * @param int $type
     * @return mixed
     */
    public function fetchWithPrinters($id, $type)
    {
        $qb = $this->createQueryBuilder();
        $this->buildDefaultQuery($qb, $id)
            ->with('teamPrinters', 'tp')
            ->with('tp.printer', 'tpp')
            ->with('tp.user', 'pu')
            ->with('tp.subCategory', 'ps');

        return $qb->getQuery()->getSingleResult($type);
    }

    /**
     * Applies filters to list queries
     *
     * @param QueryBuilder   $qb    doctrine query builder
     * @param QueryInterface $query the query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (method_exists($query, 'getTrafficAreas')) {
            $qb->andWhere($qb->expr()->in($this->alias . '.trafficArea', ':byTrafficAreas'))
                ->setParameter('byTrafficAreas', $query->getTrafficAreas());
        }
    }
}
