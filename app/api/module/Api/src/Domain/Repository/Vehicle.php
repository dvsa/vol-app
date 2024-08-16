<?php

/**
 * Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle as Entity;

/**
 * Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Vehicle extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Get a vehicle by its VRM
     *
     * @param string $vrm
     * @return array of Entity
     */
    public function fetchByVrm($vrm)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere(
            $qb->expr()->eq('m.vrm', ':vrm')
        )->setParameter('vrm', $vrm);

        $query = $qb->getQuery();
        $query->execute();

        return $query->getResult();
    }
}
