<?php

/**
 * Remove All For Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository\Query\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * Remove All For Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RemoveAllForLicence extends AbstractRawQuery
{
    protected $templateMap = [
        'lv' => LicenceVehicle::class
    ];

    protected $queryTemplate = 'UPDATE {lv}
      SET {lv.removalDate} = :removalDate,
        {lv.lastModifiedOn} = NOW(),
        {lv.lastModifiedBy} = :currentUserId
      WHERE {lv.licence} = :licence
        AND {lv.removalDate} IS NULL';

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    #[\Override]
    protected function getParams()
    {
        $today = new DateTime();

        return [
            'removalDate' => $today->format('Y-m-d H:i:s')
        ];
    }
}
