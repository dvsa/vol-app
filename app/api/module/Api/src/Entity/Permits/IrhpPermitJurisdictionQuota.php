<?php

namespace Dvsa\Olcs\Api\Entity\Permits;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;

/**
 * IrhpPermitJurisdictionQuota Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irhp_permit_jurisdiction_quota",
 *    indexes={
 *        @ORM\Index(name="fk_irhp_jurisdiction_quotas_irhp_traffic_area1_idx",
     *     columns={"traffic_area_id"}),
 *        @ORM\Index(name="fk_irhp_jurisdiction_quotas_irhp_permit_stocks1_idx",
     *     columns={"irhp_permit_stock_id"}),
 *        @ORM\Index(name="fk_irhp_permit_jurisdiction_quota_created_by_user_id",
     *     columns={"created_by"}),
 *        @ORM\Index(name="fk_irhp_permit_jurisdiction_quota_last_modified_by_user_id",
     *     columns={"last_modified_by"})
 *    }
 * )
 */
class IrhpPermitJurisdictionQuota extends AbstractIrhpPermitJurisdictionQuota
{
    /**
     * Creates a jurisdiction quota record
     *
     * @param TrafficArea     $trafficArea     traffic area
     * @param IrhpPermitStock $irhpPermitStock permit stock
     *
     * @return IrhpPermitJurisdictionQuota
     */
    public static function create(TrafficArea $trafficArea, IrhpPermitStock $irhpPermitStock)
    {
        $instance = new self();

        $instance->trafficArea = $trafficArea;
        $instance->irhpPermitStock = $irhpPermitStock;

        return $instance;
    }

    /**
     * Update the quota
     *
     * @param int $quotaNumber quota number
     *
     * @return void
     */
    public function update($quotaNumber)
    {
        $this->quotaNumber = $quotaNumber;
    }
}
