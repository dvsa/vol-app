<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\Discs;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;

/**
 * CeaseGoodsDiscsForApplication
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CeaseGoodsDiscsForApplication extends AbstractRawQuery
{
    protected $templateMap = [
        'gd' => GoodsDisc::class,
        'lv' => LicenceVehicle::class
    ];

    protected $queryTemplate = 'UPDATE {gd}
      INNER JOIN {lv} ON {lv.id} = {gd.licenceVehicle}
      SET {gd.ceasedDate} = :ceasedDate, {gd.lastModifiedOn} = NOW(), {gd.lastModifiedBy} = :currentUserId
      WHERE {gd.ceasedDate} IS NULL
      AND {lv.specifiedDate} IS NOT NULL
      AND {lv.removalDate} IS NULL
      AND {lv.interimApplication} IS NULL
      AND ({lv.application} <> :application OR {lv.application} IS NULL)
      AND {lv.licence} = :licence';

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
