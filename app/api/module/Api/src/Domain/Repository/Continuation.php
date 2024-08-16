<?php

/**
 * Continuation
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Licence\Continuation as Entity;

/**
 * Continuation
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Continuation extends AbstractRepository
{
    protected $entity = Entity::class;

    public function fetchWithTa($id)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->with('trafficArea', 'ta')
            ->byId($id);

        return $qb->getQuery()->getSingleResult();
    }

    public function fetchContinuation($month, $year, $trafficArea)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->with('trafficArea', 'ta');

        $qb->andWhere($qb->expr()->eq($this->alias . '.month', ':month'))
            ->setParameter('month', $month);
        $qb->andWhere($qb->expr()->eq($this->alias . '.year', ':year'))
            ->setParameter('year', $year);
        $qb->andWhere($qb->expr()->eq('ta.id', ':trafficArea'))
            ->setParameter('trafficArea', $trafficArea);

        return $qb->getQuery()->getResult();
    }
}
