<?php

/**
 * Transaction Amount formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\TransactionAmount;

/**
 * Transaction Amount formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransactionAmountTest extends \PHPUnit\Framework\TestCase
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
        $this->assertSame($expected, (new TransactionAmount())->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            [[],[], ''],
            [['amount' => 25],['name' => 'amount'], '£25.00'],
            [['amount' => 251.40],['name' => 'amount'], '£251.40'],
            [
                [
                    'status' => ['id' => RefData::TRANSACTION_STATUS_CANCELLED],
                    'amount' => 251.40
                ],
                ['name' => 'amount'],
                '<span class="void">£251.40</span>'
            ],
        ];
    }
}
