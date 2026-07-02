<?php

/**
 * Task date formatter tests
 *
 * @author Nick payne <nick.payne@valtech.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\TaskDate;

/**
 * Task date formatter tests
 *
 * @author Nick payne <nick.payne@valtech.co.uk>
 */
class TaskDateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provider
     */
    public function testFormat($data, $column, $expected): void
    {
        $this->assertEquals($expected, (new TaskDate(new Date()))->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'non-urgent' => [
                [
                    'date' => '2013-01-01',
                ],
                [
                    'dateformat' => 'd/m/Y',
                    'name' => 'date',
                ],
                '01/01/2013',
            ],
            'urgent' => [
                [
                    'date' => '2013-01-01',
                    'urgent' => 'Y',
                    'isClosed' => 'N',
                ],
                [
                    'dateformat' => 'd/m/Y',
                    'name' => 'date',
                ],
                '01/01/2013 (urgent)',
            ],
            'closed non-urgent' => [
                [
                    'date' => '2013-01-01',
                    'isClosed' => 'Y',
                    'urgent' => 'N',
                ],
                [
                    'dateformat' => 'd/m/Y',
                    'name' => 'date',
                ],
                '01/01/2013 <span class="status red">closed</span>',
            ],
            'closed urgent' => [
                [
                    'date' => '2013-01-01',
                    'isClosed' => 'Y',
                    'urgent' => 'Y',
                ],
                [
                    'dateformat' => 'd/m/Y',
                    'name' => 'date',
                ],
                '01/01/2013 (urgent) <span class="status red">closed</span>',
            ],
        ];
    }
}
