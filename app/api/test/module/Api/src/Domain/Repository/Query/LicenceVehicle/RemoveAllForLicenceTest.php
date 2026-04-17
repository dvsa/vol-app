<?php

declare(strict_types=1);

/**
 * Cease Discs For Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\Repository\Query\LicenceVehicle\RemoveAllForLicence;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * Cease Discs For Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RemoveAllForLicenceTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        LicenceVehicle::class => 'licence_vehicle'
    ];

    protected $columnNameMap = [
        LicenceVehicle::class => [
            'removalDate' => [
                'column' => 'removal_date'
            ],
            'licence' => [
                'isAssocation' => true,
                'column' => 'licence_id'
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
                    'removalDate' => $today->format('Y-m-d H:i:s')
                ],
                []
            ]
        ];
    }

    protected function getSut(): RemoveAllForLicence
    {
        return new RemoveAllForLicence();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE licence_vehicle lv '
        . 'SET lv.removal_date = :removalDate, '
            . 'lv.last_modified_on = NOW(), '
            . 'lv.last_modified_by = :currentUserId '
        . 'WHERE lv.licence_id = :licence'
            . ' AND lv.removal_date IS NULL';
    }
}
