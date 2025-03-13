<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingSubject as Entity;

class MessagingSubject extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
    * Apply filters
    */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (method_exists($query, 'getOnlyActive') && $query->getOnlyActive()) {
            $qb->Where($qb->expr()->eq($this->alias . '.isActive',':isActive'))
                ->setParameter('isActive', 1);
        }
    }
}
