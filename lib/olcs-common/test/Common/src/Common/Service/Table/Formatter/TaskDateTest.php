<?php

/**
 * Task date formatter tests
 *
 * @author Nick payne <nick.payne@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\TaskDate;

/**
 * Task date formatter tests
 *
 * @author Nick payne <nick.payne@valtech.co.uk>
 */
final class TaskDateTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $column, $expected): void
    {
        $this->assertEquals($expected, new TaskDate(new Date())->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'non-urgent' => [
            [
                'date' => '2013-01-01',
            ],
            [
                'dateformat' => 'd/m/Y',
                'name' => 'date',
            ],
            '01/01/2013',
        ];
        yield 'urgent' => [
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
        ];
        yield 'closed non-urgent' => [
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
        ];
        yield 'closed urgent' => [
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
        ];
    }
}
