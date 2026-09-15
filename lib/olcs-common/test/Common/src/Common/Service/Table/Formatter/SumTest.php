<?php

/**
 * Sum formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\Sum;

/**
 * Sum formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class SumTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('SumFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $column, $expected): void
    {
        $this->assertSame($expected, new Sum()->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield [[], [], '0'];
        yield [[], ['name' => 'subTotal'], '0'];
        yield [[['subTotal' => 'A'], ['subTotal' => 'B']], ['name' => 'subTotal'], '0'];
        yield [[['subTotal' => 5]], ['name' => 'subTotal'], '5'];
        yield [[['subTotal' => 5], ['subTotal' => 7]], ['name' => 'subTotal'], '12'];
        yield [
            [
                ['subTotal' => 5],
                ['subTotal' => 7],
                ['subTotal' => 'A']
            ],
            ['name' => 'subTotal'],
            '12'
        ];
        yield [
            [
                ['subTotal' => 5],
                ['subTotal' => 7],
                ['subTotal' => 95]
            ],
            ['name' => 'subTotal'],
            '107'
        ];
        yield [
            [
                ['subTotal' => '5.50'],
                ['subTotal' => '7'],
                ['subTotal' => '95.21']
            ],
            ['name' => 'subTotal'],
            '107.71'
        ];
    }
}
