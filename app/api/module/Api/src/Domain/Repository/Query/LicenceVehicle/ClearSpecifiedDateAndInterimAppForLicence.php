<?php

/**
 * Clear Specified Date And Interim App For Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository\Query\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * Clear Specified Date And Interim App For Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ClearSpecifiedDateAndInterimAppForLicence extends AbstractRawQuery
{
    protected $templateMap = [
        'lv' => LicenceVehicle::class
    ];

    protected $queryTemplate = 'UPDATE {lv}
      SET {lv.specifiedDate} = null, 
        {lv.interimApplication} = null,
        {lv.lastModifiedOn} = NOW(), 
        {lv.lastModifiedBy} = :currentUserId
      WHERE 
        {lv.licence} = :licence
        AND {lv.removalDate} IS NULL
        AND {lv.interimApplication} = :application';
}
