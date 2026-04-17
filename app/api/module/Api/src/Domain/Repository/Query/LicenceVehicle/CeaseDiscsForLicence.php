<?php

/**
 * Cease Discs For Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository\Query\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;

/**
 * Cease Discs For Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CeaseDiscsForLicence extends AbstractRawQuery
{
    protected $templateMap = [
        'gd' => GoodsDisc::class,
        'lv' => LicenceVehicle::class
    ];

    protected $queryTemplate = 'UPDATE {gd}
      INNER JOIN {lv} ON {lv.id} = {gd.licenceVehicle}
      SET {gd.ceasedDate} = :ceasedDate, {gd.isInterim} = 0,
        {gd.lastModifiedOn} = NOW(), {gd.lastModifiedBy} = :currentUserId
      WHERE {lv.licence} = :licence
      AND {lv.removalDate} IS NULL
      AND {lv.specifiedDate} IS NOT NULL
      AND {gd.ceasedDate} IS NULL';

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getParams()
    {
        $today = new DateTime();

        return [
            'ceasedDate' => $today->format('Y-m-d H:i:s')
        ];
    }
}
