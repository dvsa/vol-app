<?php

/**
 * Sum Columns formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\SumColumns;

/**
 * Sum Columns formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class SumColumnsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('SumColumnsFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $column, $expected): void
    {
        $this->assertSame($expected, new SumColumns()->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield [[], [], '0'];
        yield [['a' => 1, 'b' => 2], ['columns' => ['a', 'b']], '3'];
        yield [['a' => 1, 'b' => 2], ['columns' => ['a']], '1'];
        yield [['a' => 1], ['columns' => ['b']], '0'];
    }
}
