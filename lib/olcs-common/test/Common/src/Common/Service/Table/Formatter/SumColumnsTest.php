<?php

/**
 * Sum Columns formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\SumColumns;

/**
 * Sum Columns formatter test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SumColumnsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     * @group SumColumnsFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $column, $expected): void
    {
        $this->assertSame($expected, (new SumColumns())->format($data, $column));
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
            [['a' => 1, 'b' => 2], ['columns' => ['a', 'b']], '3'],
            [['a' => 1, 'b' => 2], ['columns' => ['a']], '1'],
            [['a' => 1], ['columns' => ['b']], '0'],
        ];
    }
}
