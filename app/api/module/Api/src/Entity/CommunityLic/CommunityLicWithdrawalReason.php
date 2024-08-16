<?php

namespace Dvsa\Olcs\Api\Entity\CommunityLic;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommunityLicWithdrawalReason Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="community_lic_withdrawal_reason",
 *    indexes={
 *        @ORM\Index(name="ix_community_lic_withdrawal_reason_community_lic_withdrawal_id",
 *          columns={"community_lic_withdrawal_id"}),
 *        @ORM\Index(name="ix_community_lic_withdrawal_reason_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_community_lic_withdrawal_reason_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_community_lic_withdrawal_reason_ref_data1_idx", columns={"type_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_community_lic_withdrawal_reason_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class CommunityLicWithdrawalReason extends AbstractCommunityLicWithdrawalReason
{
    /**
     * Withdrawal
     *
     * @param \Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicWithdrawal $withdrawal withdrawal
     * @param \Dvsa\Olcs\Api\Entity\System\RefData                      $type       type
     */
    public function __construct($withdrawal, $type)
    {
        $this->communityLicWithdrawal = $withdrawal;
        $this->type = $type;
    }
}
