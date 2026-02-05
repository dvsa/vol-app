<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\Discs;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * CreateGoodsDiscs
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateGoodsDiscs extends AbstractRawQuery
{
    protected $templateMap = [
        'gd' => GoodsDisc::class,
        'lv' => LicenceVehicle::class,
    ];

    protected $queryTemplate = 'SELECT {lv.id}, :isCopy, NOW(), :currentUserId FROM {lv}
        WHERE {lv.specifiedDate} IS NOT NULL
        AND {lv.removalDate} IS NULL
        AND {lv.interimApplication} IS NULL
        AND ({lv.application} <> :application OR {lv.application} IS NULL)
        AND {lv.licence} = :licence';

    #[\Override]
    protected function getQueryTemplate()
    {
        // build query in two parts, as Insert part cannot use aliases
        return $this->buildQueryFromTemplate(
            'INSERT INTO {gd} ({gd.licenceVehicle}, {gd.isCopy}, {gd.createdOn}, {gd.createdBy}) ',
            false
        ) . parent::getQueryTemplate();
    }
}
