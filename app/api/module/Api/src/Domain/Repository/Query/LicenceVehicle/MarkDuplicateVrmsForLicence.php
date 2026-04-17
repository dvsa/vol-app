<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;

/**
 * Mark VRM's on other licences as duplicates
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class MarkDuplicateVrmsForLicence extends AbstractRawQuery
{
    protected $templateMap = [
        'lv' => \Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle::class,
        'v' => \Dvsa\Olcs\Api\Entity\Vehicle\Vehicle::class,
        'l' => \Dvsa\Olcs\Api\Entity\Licence\Licence::class,
    ];

    protected $queryTemplate = "UPDATE {lv}
    JOIN {v} ON {lv.vehicle} = {v.id}
    JOIN {l} ON {lv.licence} = {l.id}
        SET
            {lv.warningLetterSeedDate} = NOW(),
            {lv.warningLetterSentDate} = NULL,
            {lv.lastModifiedOn} = NOW(),
            {lv.lastModifiedBy} = :currentUserId
    WHERE
        {v.vrm} IN (:vrms)
        AND {lv.specifiedDate} IS NOT NULL
        AND {lv.removalDate} IS NULL
        AND {lv.licence} <> :licence
        AND {l.goodsOrPsv} = :goodsOrPsv
        AND {l.status} IN (:licenceStatuses)";

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getParams()
    {
        return [
            'goodsOrPsv' => \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            'licenceStatuses' => [
                \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_CURTAILED,
                \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_VALID,
                \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_SUSPENDED,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getParamTypes()
    {
        return [
            'vrms' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY,
            'licenceStatuses' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ];
    }
}
