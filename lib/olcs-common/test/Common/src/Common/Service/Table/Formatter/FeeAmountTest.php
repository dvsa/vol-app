<?php

/**
 * Fee Amount formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

/**
 * Fee Amount formatter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class FeeAmountTest extends \PHPUnit\Framework\TestCase
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
        $this->assertSame($expected, new \Common\Service\Table\Formatter\FeeAmount()->format($data, $column));
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
                'amount' => 120,
                'vatAmount' => 20,
            ],
            ['name' => 'amount'],
            '£120.00<span class="status orange">includes VAT</span>'
        ];
    }
}
