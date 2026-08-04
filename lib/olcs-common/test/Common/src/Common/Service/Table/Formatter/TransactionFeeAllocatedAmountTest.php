<?php

/**
 * Transaction fee allocated amount formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\TransactionFeeAllocatedAmount as Sut;

/**
 * Transaction fee allocated amount formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class TransactionFeeAllocatedAmountTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('FeeStatusFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new Sut()->format($data, ['name' => 'amount']));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'standard' => [
            [
                'amount' => '100',
                'reversingTransaction' => null,
            ],
            '£100.00',
        ];
        yield 'reversed' => [
            [
                'amount' => '100',
                'reversingTransaction' => ['id' => 99],
            ],
            '<span class="void">£100.00</span>',
        ];
    }
}
