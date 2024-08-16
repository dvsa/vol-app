<?php

/**
 * BusShortNotice
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Bus\BusShortNotice as Entity;

/**
 * BusShortNotice
 */
class BusShortNotice extends AbstractRepository
{
    protected $entity = Entity::class;

    public function fetchByBusReg($query)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('busReg', 'b');

        $qb->andWhere($qb->expr()->eq('b.id', ':busReg'))
            ->setParameter('busReg', $query->getId());

        return $qb->getQuery()->execute();
    }
}
