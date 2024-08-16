<?php

namespace Dvsa\Olcs\Api\Entity\CompaniesHouse;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * CompaniesHouseAlert Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="companies_house_alert",
 *    indexes={
 *        @ORM\Index(name="ix_companies_house_alert_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_companies_house_alert_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_companies_house_alert_organisation_id", columns={"organisation_id"})
 *    }
 * )
 */
class CompaniesHouseAlert extends AbstractCompaniesHouseAlert
{
    public const REASON_STATUS_CHANGE  = 'company_status_change';
    public const REASON_NAME_CHANGE    = 'company_name_change';
    public const REASON_ADDRESS_CHANGE = 'company_address_change';
    public const REASON_PEOPLE_CHANGE  = 'company_people_change';
    public const REASON_INVALID_COMPANY_NUMBER = 'invalid_company_number';

    public function addReason(RefData $reason)
    {
        $alertReason = new CompaniesHouseAlertReason($reason);
        $alertReason->setCompaniesHouseAlert($this);
        $this->getReasons()->add($alertReason);
        return $this;
    }
}
