<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Permits;

use Doctrine\DBAL\Connection;
use Dvsa\Olcs\Api\Domain\Repository\Query\Permits\ExpireIrhpPermits as ExpireIrhpPermitsQry;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * ExpireIrhpPermits test
 */
class ExpireIrhpPermitsTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        IrhpPermit::class => 'ip_table',
        IrhpPermitRange::class => 'ipr_table',
        IrhpPermitStock::class => 'ips_table',
    ];

    protected $columnNameMap = [
        IrhpPermit::class => [
            'status' => [
                'column' => 'status'
            ],
            'expiryDate' => [
                'column' => 'expiry_date'
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
            'irhpPermitRange' => [
                'column' => 'irhp_permit_range_id'
            ],
        ],
        IrhpPermitRange::class => [
            'id' => [
                'column' => 'id'
            ],
            'irhpPermitStock' => [
                'column' => 'irhp_permit_stock_id'
            ],
        ],
        IrhpPermitStock::class => [
            'id' => [
                'column' => 'id'
            ],
            'irhpPermitType' => [
                'column' => 'irhp_permit_type_id'
            ],
            'validTo' => [
                'column' => 'valid_to'
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
                    'expiredStatus' => IrhpPermit::STATUS_EXPIRED,
                    'validStatuses' => IrhpPermit::$validStatuses,
                    'ecmtRemovalTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                    'endDate' => $today->format('Y-m-d'),
                    'currentUserId' => 1,
                ],
                [
                    'validStatuses' => Connection::PARAM_STR_ARRAY,
                ]
            ]
        ];
    }

    protected function getSut(): ExpireIrhpPermitsQry
    {
        return new ExpireIrhpPermitsQry();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE ip_table ip '
            . 'INNER JOIN ipr_table ipr ON ipr.id = ip.irhp_permit_range_id '
            . 'INNER JOIN ips_table ips ON ips.id = ipr.irhp_permit_stock_id '
            . 'SET ip.status = :expiredStatus, '
            . 'ip.expiry_date = NOW(), '
            . 'ip.last_modified_on = NOW(), '
            . 'ip.last_modified_by = :currentUserId, '
            . 'ip.version = ip.version + 1 '
            . 'WHERE ip.status IN (:validStatuses) '
            . 'AND ( '
                . '( '
                    . 'ips.irhp_permit_type_id = :ecmtRemovalTypeId '
                    . 'AND ip.expiry_date IS NOT NULL '
                    . 'AND ip.expiry_date < :endDate '
                . ') '
                . 'OR ( '
                    . 'ips.irhp_permit_type_id != :ecmtRemovalTypeId '
                    . 'AND ips.valid_to IS NOT NULL '
                    . 'AND ips.valid_to < :endDate '
                . ') '
            . ')';
    }
}
