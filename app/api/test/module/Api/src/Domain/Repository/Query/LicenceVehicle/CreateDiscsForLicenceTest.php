<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Repository\Query\LicenceVehicle\CreateDiscsForLicence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * Create Discs For Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateDiscsForLicenceTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        GoodsDisc::class => 'goods_disc',
        LicenceVehicle::class => 'licence_vehicle'
    ];

    protected $columnNameMap = [
        GoodsDisc::class => [
            'licenceVehicle' => [
                'isAssociation' => true,
                'column' => 'licence_vehicle_id'
            ],
            'createdOn' => [
                'column' => 'created_on'
            ],
            'createdBy' => [
                'column' => 'created_by'
            ],
        ],
        LicenceVehicle::class => [
            'id' => [
                'column' => 'id'
            ],
            'specifiedDate' => [
                'column' => 'specified_date'
            ],
            'removalDate' => [
                'column' => 'removal_date'
            ],
            'licence' => [
                'isAssocation' => true,
                'column' => 'licence_id'
            ]
        ]
    ];

    public static function paramProvider(): array
    {
        return [
            [
                [
                    'licence' => 654,
                ],
                [],
                [
                    'licence' => 654,
                ],
                []
            ]
        ];
    }

    protected function getSut(): CreateDiscsForLicence
    {
        return new CreateDiscsForLicence();
    }

    protected function getExpectedQuery(): string
    {
        return 'INSERT INTO goods_disc (licence_vehicle_id, created_on, created_by) '
        . 'SELECT lv.id, NOW(), :currentUserId '
        . 'FROM licence_vehicle lv '
        . 'WHERE lv.specified_date IS NOT NULL '
            . 'AND lv.removal_date IS NULL '
            . 'AND lv.licence_id = :licence';
    }
}
