<?php

declare(strict_types=1);

/**
 * Psv Discs Set Is Printing Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Discs;

use Dvsa\Olcs\Api\Domain\Repository\Query\Discs\PsvDiscsSetIsPrinting;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;
use Doctrine\DBAL\Connection;

/**
 * Psv Discs Set Is Printing Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PsvDiscsSetIsPrintingTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        PsvDisc::class => 'psv_disc'
    ];

    protected $columnNameMap = [
        PsvDisc::class => [
            'isPrinting' => [
                'column' => 'is_printing'
            ],
            'id' => [
                'column' => 'id'
            ],
            'lastModifiedOn' => [
                'column' => 'last_modified_on'
            ],
            'lastModifiedBy' => [
                'column' => 'last_modified_by'
            ],
        ],
    ];

    public static function paramProvider(): array
    {
        return [
            [
                ['isPrinting' => 1, 'ids' => [1,2]],
                ['isPrinting' => \PDO::PARAM_INT, 'ids' => Connection::PARAM_INT_ARRAY],
                ['isPrinting' => 1, 'ids' => [1,2]],
                ['isPrinting' => \PDO::PARAM_INT, 'ids' => Connection::PARAM_INT_ARRAY]
            ]
        ];
    }

    protected function getSut(): PsvDiscsSetIsPrinting
    {
        return new PsvDiscsSetIsPrinting();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE psv_disc pd '
        . 'SET pd.is_printing = :isPrinting, '
            . 'pd.last_modified_on = NOW(), '
            . 'pd.last_modified_by = :currentUserId '
        . 'WHERE pd.id IN (:ids)';
    }
}
