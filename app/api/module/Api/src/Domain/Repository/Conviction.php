<?php

/**
 * Conviction Repo
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Cases\Conviction as ConvictionEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Conviction Repo
 */
class Conviction extends AbstractRepository
{
    /**
     * @var ConvictionEntity
     */
    protected $entity = ConvictionEntity::class;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byCase'))
            ->setParameter('byCase', $query->getCase());
    }
}
