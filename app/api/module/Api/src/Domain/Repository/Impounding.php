<?php

/**
 * Impounding
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Cases\Impounding as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Impounding
 */
class Impounding extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Overridden default query to return appropriate table joins
     * @param QueryBuilder $qb
     * @param int $id
     * @return \Dvsa\Olcs\Api\Domain\QueryBuilder
     */
    #[\Override]
    protected function buildDefaultQuery(QueryBuilder $qb, $id)
    {
        return $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('presidingTc')
            ->with('impoundingLegislationTypes')
            ->with('venue')
            ->byId($id);
    }

    /**
     * Applies a case filter
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byCase'))
            ->setParameter('byCase', $query->getCase());
    }

    /**
     * Applies list joins
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('presidingTc');
    }
}
