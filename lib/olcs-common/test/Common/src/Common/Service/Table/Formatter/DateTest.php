<?php

/**
 * Date formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\DateFormat;

/**
 * Date formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('DateFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $column, $expected): void
    {
        $this->assertEquals($expected, new Date()->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield [['date' => '2013-01-01'], ['dateformat' => 'd/m/Y', 'name' => 'date'], '01/01/2013'];
        yield [['date' => '2013-12-31'], ['dateformat' => 'd/m/Y', 'name' => 'date'], '31/12/2013'];
        yield [['date' => '2013-12-31'], ['dateformat' => 'Y', 'name' => 'date'], '2013'];
        yield [['date' => '2013-12-31'], ['name' => 'date'], '31/12/2013'];
        yield [['someDate' => ['date' => '2013-12-31']], ['name' => 'someDate'], '31/12/2013'];
        yield [['date' => null], ['name' => 'date'], ''];
        yield [[], ['name' => 'date'], ''];
    }
}
