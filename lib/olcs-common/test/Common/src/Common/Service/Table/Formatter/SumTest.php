<?php

/**
 * Sum formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\Sum;

/**
 * Sum formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SumTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     * @group SumFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $column, $expected): void
    {
        $this->assertSame($expected, (new Sum())->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            [[], [], '0'],
            [[], ['name' => 'subTotal'], '0'],
            [[['subTotal' => 'A'], ['subTotal' => 'B']], ['name' => 'subTotal'], '0'],
            [[['subTotal' => 5]], ['name' => 'subTotal'], '5'],
            [[['subTotal' => 5], ['subTotal' => 7]], ['name' => 'subTotal'], '12'],
            [
                [
                    ['subTotal' => 5],
                    ['subTotal' => 7],
                    ['subTotal' => 'A']
                ],
                ['name' => 'subTotal'],
                '12'
            ],
            [
                [
                    ['subTotal' => 5],
                    ['subTotal' => 7],
                    ['subTotal' => 95]
                ],
                ['name' => 'subTotal'],
                '107'
            ],
            [
                [
                    ['subTotal' => '5.50'],
                    ['subTotal' => '7'],
                    ['subTotal' => '95.21']
                ],
                ['name' => 'subTotal'],
                '107.71'
            ],
        ];
    }
}
