<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * SeriousInfringement
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class SeriousInfringement extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':case'))
            ->setParameter('case', $query->getCase());
    }
}
