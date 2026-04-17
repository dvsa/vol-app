<?php

declare(strict_types=1);

/**
 * Expire All For Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\CommunityLicence;

use Dvsa\Olcs\Api\Domain\Repository\Query\CommunityLicence\ExpireAllForLicence;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * Expire All For Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ExpireAllForLicenceTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        CommunityLic::class => 'com_lic'
    ];

    protected $columnNameMap = [
        CommunityLic::class => [
            'status' => [
                'isAssociation' => true,
                'column' => 'status_id'
            ],
            'expiredDate' => [
                'column' => 'expired_date'
            ],
            'licence' => [
                'isAssociation' => true,
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
                    'status' => CommunityLic::STATUS_EXPIRED,
                    'expiredDate' => $today->format('Y-m-d H:i:s')
                ],
                []
            ]
        ];
    }

    protected function getSut(): ExpireAllForLicence
    {
        return new ExpireAllForLicence();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE com_lic cl '
        . 'SET cl.status_id = :status, '
            . 'cl.expired_date = :expiredDate, '
            . 'cl.last_modified_on = NOW(), '
            . 'cl.last_modified_by = :currentUserId '
        . 'WHERE cl.expired_date IS NULL '
            . 'AND cl.licence_id = :licence';
    }
}
