<?php

/**
 * Transaction Amount formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\TransactionAmount;

/**
 * Transaction Amount formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class TransactionAmountTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $column, $expected): void
    {
        $this->assertSame($expected, new TransactionAmount()->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield [[],[], ''];
        yield [['amount' => 25],['name' => 'amount'], '£25.00'];
        yield [['amount' => 251.40],['name' => 'amount'], '£251.40'];
        yield [
            [
                'status' => ['id' => RefData::TRANSACTION_STATUS_CANCELLED],
                'amount' => 251.40
            ],
            ['name' => 'amount'],
            '<span class="void">£251.40</span>'
        ];
    }
}
