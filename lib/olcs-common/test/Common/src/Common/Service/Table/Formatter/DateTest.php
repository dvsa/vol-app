<?php

/**
 * Date formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\DateFormat;

/**
 * Date formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     * @group DateFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $column, $expected): void
    {
        $this->assertEquals($expected, (new Date())->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            [['date' => '2013-01-01'], ['dateformat' => 'd/m/Y', 'name' => 'date'], '01/01/2013'],
            [['date' => '2013-12-31'], ['dateformat' => 'd/m/Y', 'name' => 'date'], '31/12/2013'],
            [['date' => '2013-12-31'], ['dateformat' => 'Y', 'name' => 'date'], '2013'],
            [['date' => '2013-12-31'], ['name' => 'date'], '31/12/2013'],
            [['someDate' => ['date' => '2013-12-31']], ['name' => 'someDate'], '31/12/2013'],
            [['date' => null], ['name' => 'date'], ''],
            [[], ['name' => 'date'], '']
        ];
    }
}
