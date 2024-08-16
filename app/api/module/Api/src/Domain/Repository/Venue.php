<?php

/**
 * Venue Repository
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Venue as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Venue Repository
 */
class Venue extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Applies a trafficArea filter
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query->getTrafficArea() !== null) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.trafficArea', ':trafficArea'))
                ->setParameter('trafficArea', $query->getTrafficArea());
        }

        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->isNull($this->alias . '.endDate'),
                $qb->expr()->gt($this->alias . '.endDate', ':today')
            )
        );

        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $qb->setParameter('today', $today);

        $qb->orderBy($this->alias . '.name', 'ASC');
    }
}
