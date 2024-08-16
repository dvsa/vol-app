<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Cases\Stay as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Stay
 */
class Stay extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Apply list filters
     *
     * @param QueryBuilder   $qb    Query builder
     * @param QueryInterface $query Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byCase'))
            ->setParameter('byCase', $query->getCase());
    }

    /**
     * Apply list joins
     *
     * @param QueryBuilder $qb Query builder
     *
     * @return void
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('case');
    }
}
