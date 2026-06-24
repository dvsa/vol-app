<?php

namespace Dvsa\Olcs\Api\Entity\CommunityLic;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommunityLicSuspension Entity
 */
#[ORM\Table(name: 'community_lic_suspension')]
#[ORM\Index(name: 'ix_community_lic_suspension_community_lic_id', columns: ['community_lic_id'])]
#[ORM\Index(name: 'ix_community_lic_suspension_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_community_lic_suspension_last_modified_by', columns: ['last_modified_by'])]
#[ORM\UniqueConstraint(name: 'uk_community_lic_suspension_olbs_key', columns: ['olbs_key'])]
#[ORM\Entity]
class CommunityLicSuspension extends AbstractCommunityLicSuspension
{
    /**
     * Update community licence suspension
     *
     * @param CommunityLic $communityLic community licence
     * @param string       $startDate    start date
     * @param string       $endDate      end date
     *
     * @return void
     */
    public function updateCommunityLicSuspension($communityLic, $startDate, $endDate)
    {
        $this->communityLic = $communityLic;
        $this->startDate = new \DateTime($startDate);
        if ($endDate) {
            $this->endDate = new \DateTime($endDate);
        } else {
            $this->endDate = null;
        }
    }
}
