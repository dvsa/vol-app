<?php

/**
 * Community Licence Withdrawal Reason
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicWithdrawalReason as CommunityLicWithdrawalReasonEntity;

/**
 * Community Licence Withdrawal Reason
 */
class CommunityLicWithdrawalReason extends AbstractRepository
{
    protected $entity = CommunityLicWithdrawalReasonEntity::class;

    public function fetchByWithdrawalIds($ids)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->in($this->alias . '.communityLicWithdrawal', ':communityLicWithdrawal'))
            ->setParameter('communityLicWithdrawal', $ids);
        return $qb->getQuery()->execute();
    }
}
