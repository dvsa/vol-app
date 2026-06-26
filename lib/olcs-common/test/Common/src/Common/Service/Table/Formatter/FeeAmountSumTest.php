<?php

/**
 * Fee Amount Sum formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\FeeAmount;
use Common\Service\Table\Formatter\FeeAmountSum;
use Common\Service\Table\Formatter\Sum;

/**
 * Fee Amount Sum formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeAmountSumTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     *
     * @dataProvider provider
     */
    public function testFormat($data, $column, $expected): void
    {
        $sumFormatter = new Sum();
        $feeAmountFormatter = new FeeAmount();
        $sut = new FeeAmountSum($sumFormatter, $feeAmountFormatter);
        $this->assertSame($expected, $sut->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            [[], [], null],
            [[], ['name' => 'subTotal'], '£0.00'],
            [[['subTotal' => 'A'], ['subTotal' => 'B']], ['name' => 'subTotal'], '£0.00'],
            [[['subTotal' => 5]], ['name' => 'subTotal'], '£5.00'],
            [[['subTotal' => 5], ['subTotal' => 7]], ['name' => 'subTotal'], '£12.00'],
            [
                [
                    ['subTotal' => 5],
                    ['subTotal' => 7],
                    ['subTotal' => 'A']
                ],
                ['name' => 'subTotal'],
                '£12.00'
            ],
            [
                [
                    ['subTotal' => 5],
                    ['subTotal' => 7],
                    ['subTotal' => 95]
                ],
                ['name' => 'subTotal'],
                '£107.00'
            ],
            [
                [
                    ['subTotal' => '5.11'],
                    ['subTotal' => 7],
                    ['subTotal' => '95.341']
                ],
                ['name' => 'subTotal'],
                '£107.45'
            ],
        ];
    }
}
