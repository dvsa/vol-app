<?php

declare(strict_types=1);

/**
 * PSV Discs Set Is Printing Off and Disc No Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Discs;

use Dvsa\Olcs\Api\Domain\Repository\Query\Discs\PsvDiscsSetIsPrintingOffAndDiscNo;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * PSV Discs Set Is Printing Off and Disc No Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class PsvDiscsSetIsPrintingOffAndDiscNoTest extends AbstractDbQueryTestCase
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

    public static function paramProvider(): \Iterator
    {
        $today = new DateTime();
        yield [
            ['ids' => [1,2], 'startNumber' => 1],
            ['ids' => \Doctrine\DBAL\ArrayParameterType::INTEGER, 'startNumber' => \Doctrine\DBAL\ParameterType::INTEGER],
            [
                'issuedDate' => $today->format('Y-m-d H:i:s'),
                'ids' => [1,2],
                'startNumber' => 1
            ],
            [
                'issuedDate' => \Doctrine\DBAL\ParameterType::STRING,
                'ids' => \Doctrine\DBAL\ArrayParameterType::INTEGER,
                'startNumber' => \Doctrine\DBAL\ParameterType::INTEGER]
        ];
    }

    protected function getSut(): PsvDiscsSetIsPrintingOffAndDiscNo
    {
        return new PsvDiscsSetIsPrintingOffAndDiscNo();
    }

    protected function getExpectedQuery(): string
    {
        return 'UPDATE psv_disc pd '
        . 'SET pd.is_printing = 0, '
            . 'pd.disc_no = :discNo, '
            . 'pd.issued_date = :issuedDate, '
            . 'pd.last_modified_on = NOW(), '
            . 'pd.last_modified_by = :currentUserId '
        . 'WHERE pd.id = :id';
    }
}
