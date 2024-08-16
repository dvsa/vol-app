<?php

/**
 * Vehicle History View
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\View\VehicleHistoryView as Entity;

/**
 * Vehicle History View
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehicleHistoryView extends AbstractRepository
{
    protected $entity = Entity::class;

    public function fetchByVrm($vrm)
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq('m.vrm', ':vrm'));
        $qb->andWhere($qb->expr()->isNotNull('m.id'));
        $qb->setParameter('vrm', $vrm);

        $qb->orderBy('m.specifiedDate', 'DESC');

        $query = $qb->getQuery();
        $query->execute();

        return $query->getArrayResult();
    }
}
