<?php

/**
 * Community Licence Suspension
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspension as CommunityLicSuspensionEntity;

/**
 * Community Licence Suspension
 */
class CommunityLicSuspension extends AbstractRepository
{
    protected $entity = CommunityLicSuspensionEntity::class;

    public function fetchByCommunityLicIds($ids)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->in($this->alias . '.communityLic', ':communityLic'))
            ->setParameter('communityLic', $ids);
        return $qb->getQuery()->execute();
    }
}
