<?php

/**
 * Prohibition Entity
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Prohibition\Prohibition as ProhibitionEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Prohibition Entity
 */
class Prohibition extends AbstractRepository
{
    /**
     * @var ProhibitionEntity
     */
    protected $entity = ProhibitionEntity::class;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byCase'))
            ->setParameter('byCase', $query->getCase());
    }
}
