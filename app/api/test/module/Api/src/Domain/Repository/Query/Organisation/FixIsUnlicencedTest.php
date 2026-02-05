<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Organisation;

use Dvsa\Olcs\Api\Domain\Repository\Query\Organisation\FixIsUnlicenced;
use Dvsa\Olcs\Api\Entity;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * Class FixIsUnlicencedTest
 */
class FixIsUnlicencedTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        Entity\Organisation\Organisation::class => 'organisation',
        Entity\Licence\Licence::class => 'licence',
    ];

    protected $columnNameMap = [
        Entity\Organisation\Organisation::class => [
            'id' => [
                'column' => 'id'
            ],
            'isUnlicensed' => [
                'column' => 'is_unlicensed'
            ],
            'lastModifiedOn' => [
                'column' => 'last_modified_on'
            ],
            'lastModifiedBy' => [
                'column' => 'last_modified_by'
            ],
        ],
        Entity\Licence\Licence::class => [
            'status' => [
                'column' => 'status'
            ],
            'organisation' => [
                'column' => 'organisation_id'
            ],
        ],
    ];

    public static function paramProvider(): array
    {
        return [
            [
                [],
                [],
                [],
                []
            ]
        ];
    }

    protected function getSut(): FixIsUnlicenced
    {
        return new FixIsUnlicenced();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE organisation o ' .
            'SET o.is_unlicensed = 0, o.last_modified_on = NOW(), o.last_modified_by = :currentUserId ' .
            'WHERE o.is_unlicensed = 1 AND o.id NOT IN ' .
                '( SELECT l.organisation_id FROM licence l WHERE l.status = \'lsts_unlicenced\' )';
    }
}
