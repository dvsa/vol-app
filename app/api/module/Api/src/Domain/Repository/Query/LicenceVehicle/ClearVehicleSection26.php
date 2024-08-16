<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;

/**
 * Clear the section26 flag on vehicles linked to a licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ClearVehicleSection26 extends AbstractRawQuery
{
    protected $templateMap = [
        'v' => Vehicle::class,
        'lv' => LicenceVehicle::class
    ];

    protected $queryTemplate = 'UPDATE {v}
      INNER JOIN {lv} ON {lv.vehicle} = {v.id}
      SET {v.section26} = 0, {v.lastModifiedOn} = NOW(), {v.lastModifiedBy} = :currentUserId
      WHERE {lv.licence} = :licence
      AND {v.section26} <> 0';
}
