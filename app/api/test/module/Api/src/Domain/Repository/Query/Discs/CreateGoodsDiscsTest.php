<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Discs;

use Dvsa\Olcs\Api\Domain\Repository\Query\Discs\CreateGoodsDiscs;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * CreateGoodsDiscsTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateGoodsDiscsTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        GoodsDisc::class => 'goods_disc',
        LicenceVehicle::class => 'licence_vehicle',
    ];

    protected $columnNameMap = [
        GoodsDisc::class => [
            'licenceVehicle' => [
                'isAssociation' => true,
                'column' => 'licence_vehicle_id'
            ],
            'isCopy' => [
                'column' => 'is_copy'
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
            'interimApplication' => [
                'column' => 'interim_application'
            ],
            'application' => [
                'isAssociation' => true,
                'column' => 'application_id'
            ],
            'licence' => [
                'isAssociation' => true,
                'column' => 'licence_id'
            ],
        ],
    ];

    public static function paramProvider(): array
    {
        return [
            [
                [
                    'licence' => 1102,
                    'applciation' => 321,
                    'isCopy' => 1,
                ],
                [],
                [
                    'licence' => 1102,
                    'applciation' => 321,
                    'isCopy' => 1,
                ],
                []
            ]
        ];
    }

    protected function getSut(): CreateGoodsDiscs
    {
        return new CreateGoodsDiscs();
    }

    protected function getExpectedQuery(): string
    {
        return 'INSERT INTO goods_disc (licence_vehicle_id, is_copy, created_on, created_by) '
        . 'SELECT lv.id, :isCopy, NOW(), :currentUserId '
        . 'FROM licence_vehicle lv '
        . 'WHERE lv.specified_date IS NOT NULL '
            . 'AND lv.removal_date IS NULL '
            . 'AND lv.interim_application IS NULL '
            . 'AND (lv.application_id <> :application OR lv.application_id IS NULL) '
            . 'AND lv.licence_id = :licence';
    }
}
