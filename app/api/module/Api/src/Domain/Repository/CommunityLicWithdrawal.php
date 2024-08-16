<?php

/**
 * Community Licence Withdrawal
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicWithdrawal as CommunityLicWithdrawalEntity;

/**
 * Community Licence Withdrawal
 */
class CommunityLicWithdrawal extends AbstractRepository
{
    protected $entity = CommunityLicWithdrawalEntity::class;

    public function fetchByCommunityLicIds($ids)
    {
        $qb = $this->createQueryBuilder();
        $qb->andWhere($qb->expr()->in($this->alias . '.communityLic', ':communityLic'))
            ->setParameter('communityLic', $ids);
        return $qb->getQuery()->execute();
    }
}
