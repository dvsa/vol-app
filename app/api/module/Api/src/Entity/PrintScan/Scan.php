<?php

namespace Dvsa\Olcs\Api\Entity\PrintScan;

use Doctrine\ORM\Mapping as ORM;

/**
 * Scan Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="scan",
 *    indexes={
 *        @ORM\Index(name="ix_scan_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_scan_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_scan_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_scan_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_scan_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="ix_scan_sub_category_id", columns={"sub_category_id"}),
 *        @ORM\Index(name="ix_scan_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_scan_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_scan_category_id", columns={"category_id"}),
 *        @ORM\Index(name="ix_scan_irfo_organisation_id", columns={"irfo_organisation_id"}),
 *        @ORM\Index(name="ix_scan_irhp_application_id", columns={"irhp_application_id"})
 *    }
 * )
 */
class Scan extends AbstractScan
{
    /**
     * Whether this scan is a back scan
     *
     * @return bool
     */
    public function isBackScan()
    {
        return !is_null($this->dateReceived);
    }
}
