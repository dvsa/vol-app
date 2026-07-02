<?php

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\FinancialHistory;

class FinancialHistoryTest extends \PHPUnit\Framework\TestCase
{
    public function testMapFromResult(): void
    {
        $input = [
            'foo' => 'bar',
            'insolvencyConfirmation' => 'FOO',
        ];

        $output = FinancialHistory::mapFromResult($input);

        $expected = [
            'data' => [
                'foo' => 'bar',
                'insolvencyConfirmation' => 'FOO',
                'financialHistoryConfirmation' => [
                    'insolvencyConfirmation' => 'FOO'
                ]
            ]
        ];

        $this->assertEquals($expected, $output);
    }
}
