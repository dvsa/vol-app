<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Organisation;

use Dvsa\Olcs\Api\Domain\Repository\Query\Organisation\FixIsIrfo;
use Dvsa\Olcs\Api\Entity;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * Class FixIsIrfoTest
 */
class FixIsIrfoTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        Entity\Organisation\Organisation::class => 'organisation',
        Entity\Irfo\IrfoPsvAuth::class => 'irfo_psv_auth',
        Entity\Irfo\IrfoGvPermit::class => 'irfo_gv_permit',
    ];

    protected $columnNameMap = [
        Entity\Organisation\Organisation::class => [
            'id' => [
                'column' => 'id'
            ],
            'type' => [
                'column' => 'type'
            ],
            'isIrfo' => [
                'column' => 'is_irfo'
            ],
            'lastModifiedOn' => [
                'column' => 'last_modified_on'
            ],
            'lastModifiedBy' => [
                'column' => 'last_modified_by'
            ],
        ],
        Entity\Irfo\IrfoPsvAuth::class => [
            'id' => [
                'column' => 'id'
            ],
            'organisation' => [
                'column' => 'organisation_id'
            ],
        ],
        Entity\Irfo\IrfoGvPermit::class => [
            'id' => [
                'column' => 'id'
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

    protected function getSut(): FixIsIrfo
    {
        return new FixIsIrfo();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE organisation o '
            . 'LEFT JOIN irfo_psv_auth ipa ON ipa.organisation_id = o.id '
            . 'LEFT JOIN irfo_gv_permit igp ON igp.organisation_id = o.id '
            . 'SET o.is_irfo = 0, '
                . 'o.last_modified_on = NOW(), '
                . 'o.last_modified_by = :currentUserId '
            . 'WHERE o.type <> \'org_t_ir\' '
                . 'AND ipa.id IS NULL '
                . 'AND igp.id IS NULL '
                . 'AND o.is_irfo <> 0;';
    }
}
