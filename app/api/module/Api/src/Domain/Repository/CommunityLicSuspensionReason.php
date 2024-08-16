<?php

/**
 * Community Licence Suspension Reason
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspensionReason as CommunityLicSuspensionReasonEntity;

/**
 * Community Licence Suspension Reason
 */
class CommunityLicSuspensionReason extends AbstractRepository
{
    protected $entity = CommunityLicSuspensionReasonEntity::class;

    public function fetchBySuspensionIds($ids)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->in($this->alias . '.communityLicSuspension', ':communityLicSuspension'))
            ->setParameter('communityLicSuspension', $ids);
        return $qb->getQuery()->execute();
    }
}
