<?php

declare(strict_types=1);

/**
 * Goods Discs Set Is Printing Off and Disc No Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Discs;

use Dvsa\Olcs\Api\Domain\Repository\Query\Discs\GoodsDiscsSetIsPrintingOffAndDiscNo;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;
use Doctrine\DBAL\Connection;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Goods Discs Set Is Printing Off and Disc No Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GoodsDiscsSetIsPrintingOffAndDiscNoTest extends AbstractDbQueryTestCase
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
            'discNo' => [
                'column' => 'disc_no'
            ],
            'issuedDate' => [
                'column' => 'issued_date'
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
        $today = new DateTime();
        return [
            [
                ['ids' => [1,2], 'startNumber' => 1],
                ['ids' => Connection::PARAM_INT_ARRAY, 'startNumber' => \PDO::PARAM_INT],
                [
                    'issuedDate' => $today->format('Y-m-d H:i:s'),
                    'ids' => [1,2],
                    'startNumber' => 1
                ],
                [
                    'issuedDate' => \PDO::PARAM_STR,
                    'ids' => Connection::PARAM_INT_ARRAY,
                    'startNumber' => \PDO::PARAM_INT]
            ]
        ];
    }

    protected function getSut(): GoodsDiscsSetIsPrintingOffAndDiscNo
    {
        return new GoodsDiscsSetIsPrintingOffAndDiscNo();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE goods_disc gd '
        . 'SET gd.is_printing = 0, '
            . 'gd.disc_no = :discNo, '
            . 'gd.issued_date = :issuedDate, '
            . 'gd.last_modified_on = NOW(), '
            . 'gd.last_modified_by = :currentUserId '
        . 'WHERE gd.id = :id';
    }
}
