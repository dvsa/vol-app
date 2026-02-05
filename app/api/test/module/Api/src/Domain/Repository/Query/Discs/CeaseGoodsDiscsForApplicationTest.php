<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Discs;

use Dvsa\Olcs\Api\Domain\Repository\Query\Discs\CeaseGoodsDiscsForApplication;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * CeaseGoodsDiscsForApplicationTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CeaseGoodsDiscsForApplicationTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        GoodsDisc::class => 'goods_disc',
        LicenceVehicle::class => 'licence_vehicle',
    ];

    protected $columnNameMap = [
        GoodsDisc::class => [
            'licenceVehicle' => [
                'column' => 'licence_vehicle'
            ],
            'ceasedDate' => [
                'column' => 'ceased_date'
            ],
            'lastModifiedOn' => [
                'column' => 'last_modified_on'
            ],
            'lastModifiedBy' => [
                'column' => 'last_modified_by'
            ],
        ],
        LicenceVehicle::class => [
            'id' => [
                'column' => 'id'
            ],
            'licence' => [
                'isAssociation' => true,
                'column' => 'licence_id'
            ],
            'application' => [
                'isAssociation' => true,
                'column' => 'application_id'
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
        ],
    ];

    public static function paramProvider(): array
    {
        $today = new DateTime();

        return [
            [
                [],
                [],
                [
                    'ceasedDate' => $today->format('Y-m-d H:i:s')
                ],
                []
            ]
        ];
    }

    protected function getSut(): CeaseGoodsDiscsForApplication
    {
        return new CeaseGoodsDiscsForApplication();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE goods_disc gd '
        . 'INNER JOIN licence_vehicle lv '
            . 'ON lv.id = gd.licence_vehicle '
        . 'SET gd.ceased_date = :ceasedDate, '
            . 'gd.last_modified_on = NOW(), '
            . 'gd.last_modified_by = :currentUserId '
        . 'WHERE gd.ceased_date IS NULL '
            . 'AND lv.specified_date IS NOT NULL '
            . 'AND lv.removal_date IS NULL '
            . 'AND lv.interim_application IS NULL '
            . 'AND (lv.application_id <> :application OR lv.application_id IS NULL) '
            . 'AND lv.licence_id = :licence';
    }
}
