<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Permits;

use Doctrine\DBAL\Connection;
use Dvsa\Olcs\Api\Domain\Repository\Query\Permits\ExpireIrhpApplications as ExpireIrhpApplicationsQry;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * ExpireIrhpApplications test
 */
class ExpireIrhpApplicationsTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        IrhpApplication::class => 'ia_table',
        IrhpPermitApplication::class => 'ipa_table',
        IrhpPermit::class => 'ip_table',
    ];

    protected $columnNameMap = [
        IrhpApplication::class => [
            'id' => [
                'column' => 'id'
            ],
            'status' => [
                'column' => 'status'
            ],
            'irhpPermitType' => [
                'column' => 'irhp_permit_type_id'
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
            'expiryDate' => [
                'column' => 'expiry_date'
            ],
        ],
        IrhpPermitApplication::class => [
            'id' => [
                'column' => 'id'
            ],
            'irhpApplication' => [
                'column' => 'irhp_application_id'
            ],
        ],
        IrhpPermit::class => [
            'id' => [
                'column' => 'id'
            ],
            'irhpPermitApplication' => [
                'column' => 'irhp_permit_application_id'
            ],
            'status' => [
                'column' => 'status'
            ],
        ],
    ];

    public static function paramProvider(): array
    {
        return [
            [
                [],
                [],
                [
                    'expiredStatus' => IrhpInterface::STATUS_EXPIRED,
                    'validStatus' => IrhpInterface::STATUS_VALID,
                    'permitValidStatuses' => IrhpPermit::$validStatuses,
                    'certificatePermitTypes' => IrhpPermitType::CERTIFICATE_TYPES,
                    'currentUserId' => 1,
                ],
                [
                    'permitValidStatuses' => Connection::PARAM_STR_ARRAY,
                    'certificatePermitTypes' => Connection::PARAM_INT_ARRAY,
                ]
            ]
        ];
    }

    protected function getSut(): ExpireIrhpApplicationsQry
    {
        return new ExpireIrhpApplicationsQry();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE ia_table ia '
            . 'SET ia.status = :expiredStatus, '
            . 'ia.expiry_date = NOW(), '
            . 'ia.last_modified_on = NOW(), '
            . 'ia.last_modified_by = :currentUserId, '
            . 'ia.version = ia.version + 1 '
            . 'WHERE ia.status = :validStatus '
            . 'AND ia.irhp_permit_type_id NOT IN (:certificatePermitTypes) '
            . 'AND ia.id NOT IN ( '
                . 'SELECT ipa.irhp_application_id '
                . 'FROM ipa_table ipa '
                . 'INNER JOIN ip_table ip '
                    . 'ON ip.irhp_permit_application_id = ipa.id '
                        . 'AND ip.status IN (:permitValidStatuses) '
                . 'WHERE ipa.irhp_application_id IS NOT NULL '
            . ')';
    }
}
