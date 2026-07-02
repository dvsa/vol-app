<?php

/**
 * Transaction fee allocated amount formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\TransactionFeeAllocatedAmount as Sut;

/**
 * Transaction fee allocated amount formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransactionFeeAllocatedAmountTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     * @group FeeStatusFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new Sut())->format($data, ['name' => 'amount']));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'standard' => [
                [
                    'amount' => '100',
                    'reversingTransaction' => null,
                ],
                '£100.00',
            ],
            'reversed' => [
                [
                    'amount' => '100',
                    'reversingTransaction' => ['id' => 99],
                ],
                '<span class="void">£100.00</span>',
            ],
        ];
    }
}
