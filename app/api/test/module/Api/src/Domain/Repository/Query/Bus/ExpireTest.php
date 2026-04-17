<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Bus;

use Dvsa\Olcs\Api\Domain\Repository\Query\Bus\Expire as ExpireBusQry;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * Expire bus reg test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ExpireTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        BusRegEntity::class => 'br_table'
    ];

    protected $columnNameMap = [
        BusRegEntity::class => [
            'status' => [
                'isAssociation' => true,
                'column' => 'status'
            ],
            'revertStatus' => [
                'isAssociation' => true,
                'column' => 'revert_status'
            ],
            'lastModifiedOn' => [
                'column' => 'last_modified_on'
            ],
            'lastModifiedBy' => [
                'column' => 'last_modified_by'
            ],
            'version' => [
                'column' => 'version'
            ],
            'endDate' => [
                'column' => 'end_date'
            ]
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
                    'expiredStatus' => BusRegEntity::STATUS_EXPIRED,
                    'registeredStatus' => BusRegEntity::STATUS_REGISTERED,
                    'endDate' => $today->format('Y-m-d')
                ],
                []
            ]
        ];
    }

    protected function getSut(): ExpireBusQry
    {
        return new ExpireBusQry();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE br_table br '
            . 'SET br.status = :expiredStatus, '
            . 'br.revert_status = :registeredStatus, '
            . 'br.last_modified_on = NOW(), '
            . 'br.last_modified_by = :currentUserId, '
            . 'br.version = br.version + 1 '
            . 'WHERE br.status = :registeredStatus '
            . 'AND br.end_date <= :endDate';
    }
}
