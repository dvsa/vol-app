<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Repository\Query\LicenceVehicle\CeaseDiscsForLicenceVehicle;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * Cease Discs For Licence Vehicle Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CeaseDiscsForLicenceVehicleTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        GoodsDisc::class => 'goods_disc'
    ];

    protected $columnNameMap = [
        GoodsDisc::class => [
            'licenceVehicle' => [
                'isAssociation' => true,
                'column' => 'licence_vehicle_id'
            ],
            'ceasedDate' => [
                'column' => 'ceased_date'
            ],
            'isInterim' => [
                'column' => 'is_interim'
            ],
            'lastModifiedOn' => [
                'column' => 'last_modified_on'
            ],
            'lastModifiedBy' => [
                'column' => 'last_modified_by'
            ],
        ]
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

    protected function getSut(): CeaseDiscsForLicenceVehicle
    {
        return new CeaseDiscsForLicenceVehicle();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE goods_disc gd '
          . 'SET gd.ceased_date = :ceasedDate, '
          . 'gd.is_interim = 0, '
          . 'gd.last_modified_on = NOW(), '
          . 'gd.last_modified_by = :currentUserId '
          . 'WHERE gd.licence_vehicle_id = :licenceVehicle '
          . 'AND gd.ceased_date IS NULL';
    }
}
