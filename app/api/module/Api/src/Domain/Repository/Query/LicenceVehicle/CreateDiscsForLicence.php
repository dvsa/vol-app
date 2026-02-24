<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * CreateDiscsForLicence
 *
 * Create a goods disc row for each active licence vehicle connected to a licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateDiscsForLicence extends AbstractRawQuery
{
    protected $templateMap = [
        'gd' => GoodsDisc::class,
        'lv' => LicenceVehicle::class,
    ];

    protected $queryTemplate = 'SELECT {lv.id}, NOW(), :currentUserId FROM {lv}
        WHERE {lv.specifiedDate} IS NOT NULL
        AND {lv.removalDate} IS NULL
        AND {lv.licence} = :licence';

    #[\Override]
    protected function getQueryTemplate()
    {
        // build query in two parts, as Insert part cannot use aliases
        return $this->buildQueryFromTemplate(
            'INSERT INTO {gd} ({gd.licenceVehicle}, {gd.createdOn}, {gd.createdBy}) ',
            false
        ) . parent::getQueryTemplate();
    }
}
