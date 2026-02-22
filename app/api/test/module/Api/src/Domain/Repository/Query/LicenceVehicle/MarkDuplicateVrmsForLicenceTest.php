<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Repository\Query\LicenceVehicle\MarkDuplicateVrmsForLicence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * MarkDuplicateVrmsForLicenceTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class MarkDuplicateVrmsForLicenceTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        LicenceVehicle::class => 'licence_vehicle',
        Vehicle::class => 'vehicle',
        Licence::class => 'licence',
    ];

    protected $columnNameMap = [
        LicenceVehicle::class => [
            'vehicle' => [
                'isAssocation' => true,
                'column' => 'vehicle_id'
            ],
            'licence' => [
                'isAssocation' => true,
                'column' => 'licence_id'
            ],
            'specifiedDate' => [
                'column' => 'specified_date'
            ],
            'removalDate' => [
                'column' => 'removal_date'
            ],
            'warningLetterSeedDate' => [
                'column' => 'warning_letter_seed_date'
            ],
            'warningLetterSentDate' => [
                'column' => 'warning_letter_sent_date'
            ],
            'lastModifiedOn' => [
                'column' => 'last_modified_on'
            ],
            'lastModifiedBy' => [
                'column' => 'last_modified_by'
            ],
        ],
        Vehicle::class => [
            'id' => [
                'column' => 'id'
            ],
            'vrm' => [
                'column' => 'vrm'
            ],
        ],
        Licence::class => [
            'id' => [
                'column' => 'id'
            ],
            'goodsOrPsv' => [
                'column' => 'goods_or_psv'
            ],
            'status' => [
                'column' => 'status'
            ],
        ]
    ];

    public static function paramProvider(): array
    {
        return [
            [
                [
                    'vrms' => ['vrm1', 'vrm2'],
                    'licence' => 402
                ],
                [],
                [
                    'goodsOrPsv' => \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceStatuses' => [
                        \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_CURTAILED,
                        \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_VALID,
                        \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_SUSPENDED,
                    ],
                    'vrms' => ['vrm1', 'vrm2'],
                    'licence' => 402,
                ],
                [
                    'vrms' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY,
                    'licenceStatuses' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
                ]
            ]
        ];
    }

    protected function getSut(): MarkDuplicateVrmsForLicence
    {
        return new MarkDuplicateVrmsForLicence();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE licence_vehicle lv '
        . 'JOIN vehicle v '
            . 'ON lv.vehicle_id = v.id '
        . 'JOIN licence l '
            . 'ON lv.licence_id = l.id '
        . 'SET lv.warning_letter_seed_date = NOW(), '
            . 'lv.warning_letter_sent_date = NULL, '
            . 'lv.last_modified_on = NOW(), '
            . 'lv.last_modified_by = :currentUserId '
        . 'WHERE v.vrm IN (:vrms) '
            . 'AND lv.specified_date IS NOT NULL '
            . 'AND lv.removal_date IS NULL '
            . 'AND lv.licence_id <> :licence '
            . 'AND l.goods_or_psv = :goodsOrPsv '
            . 'AND l.status IN (:licenceStatuses)';
    }
}
