<?php

declare(strict_types=1);

/**
 * Goods Discs Set Is Printing Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Discs;

use Dvsa\Olcs\Api\Domain\Repository\Query\Discs\GoodsDiscsSetIsPrinting;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;
use Doctrine\DBAL\Connection;

/**
 * Goods Discs Set Is Printing Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GoodsDiscsSetIsPrintingTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        GoodsDisc::class => 'goods_disc'
    ];

    protected $columnNameMap = [
        GoodsDisc::class => [
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

    protected function getSut(): GoodsDiscsSetIsPrinting
    {
        return new GoodsDiscsSetIsPrinting();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE goods_disc gd '
        . 'SET gd.is_printing = :isPrinting, '
            . 'gd.last_modified_on = NOW(), '
            . 'gd.last_modified_by = :currentUserId '
        . 'WHERE gd.id IN (:ids)';
    }
}
