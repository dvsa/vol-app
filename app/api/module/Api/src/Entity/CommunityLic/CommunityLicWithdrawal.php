<?php

namespace Dvsa\Olcs\Api\Entity\CommunityLic;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommunityLicWithdrawal Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="community_lic_withdrawal",
 *    indexes={
 *        @ORM\Index(name="ix_community_lic_withdrawal_community_lic_id", columns={"community_lic_id"}),
 *        @ORM\Index(name="ix_community_lic_withdrawal_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_community_lic_withdrawal_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_community_lic_withdrawal_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class CommunityLicWithdrawal extends AbstractCommunityLicWithdrawal
{
    /**
     * Update community licence withdrawal
     *
     * @param CommunityLic $communityLic community licence
     *
     * @return void
     */
    public function updateCommunityLicWithdrawal($communityLic)
    {
        $this->communityLic = $communityLic;
        $this->startDate = new \DateTime();
    }
}
