<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;

/**
 * Cease Discs For Licence Vehicle
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CeaseDiscsForLicenceVehicle extends AbstractRawQuery
{
    protected $templateMap = [
        'gd' => GoodsDisc::class
    ];

    protected $queryTemplate = 'UPDATE {gd}
      SET {gd.ceasedDate} = :ceasedDate,
          {gd.isInterim} = 0,
          {gd.lastModifiedOn} = NOW(),
          {gd.lastModifiedBy} = :currentUserId
      WHERE {gd.licenceVehicle} = :licenceVehicle
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
