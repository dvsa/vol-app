<?php

declare(strict_types=1);

namespace CommonTest\Filter;

use Common\Filter\DateTimeSelectNullifier;

/**
 * Date Time Select Nullifier Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DateTimeSelectNullifierTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('filter')]
    #[\PHPUnit\Framework\Attributes\Group('date_time_select_nullifier_filter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFilter')]
    public function testFilter($input, $output): void
    {
        $sut = new DateTimeSelectNullifier();
        $this->assertEquals($output, $sut->filter($input));
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function provideFilter(): \Iterator
    {
        yield [null, null];
        yield ['string', null];
        yield [['day' => '', 'year' => '', 'month' => '', 'hour' => '', 'minute' => ''], null];
        yield [['day' => '', 'year' => '2012', 'month' => '10', 'hour' => '16', 'minute' => ''], '2012-10- 16::00'];
        yield [['day' => '', 'year' => '', 'month' => '10', 'hour' => '16', 'minute' => ''], '-10- 16::00'];
        yield [['day' => '', 'year' => '', 'month' => '', 'hour' => '16', 'minute' => ''], '-- 16::00'];
        yield [['day' => '04', 'year' => '2012', 'month' => '10', 'hour' => '16', 'minute' => ''], '2012-10-04 16::00'];
        yield [['day' => '04', 'year' => '2012', 'month' => '10', 'hour' => '16', 'minute' => '00'], '2012-10-04 16:00:00'];
    }
}
