<?php

namespace CommonTest\Filter;

use Common\Filter\DateTimeSelectNullifier;

/**
 * Date Time Select Nullifier Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DateTimeSelectNullifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group filter
     * @group date_time_select_nullifier_filter
     * @dataProvider provideFilter
     */
    public function testFilter($input, $output): void
    {
        $sut = new DateTimeSelectNullifier();
        $this->assertEquals($output, $sut->filter($input));
    }

    /**
     * @return array
     */
    public function provideFilter()
    {
        return [
            [null, null],
            ['string', null],
            [['day' => '', 'year' => '', 'month' => '', 'hour' => '', 'minute' => ''], null],
            [['day' => '', 'year' => '2012', 'month' => '10', 'hour' => '16', 'minute' => ''], '2012-10- 16::00'],
            [['day' => '', 'year' => '', 'month' => '10', 'hour' => '16', 'minute' => ''], '-10- 16::00'],
            [['day' => '', 'year' => '', 'month' => '', 'hour' => '16', 'minute' => ''], '-- 16::00'],
            [['day' => '04', 'year' => '2012', 'month' => '10', 'hour' => '16', 'minute' => ''], '2012-10-04 16::00'],
            [['day' => '04', 'year' => '2012', 'month' => '10', 'hour' => '16', 'minute' => '00'], '2012-10-04 16:00:00'],
        ];
    }
}
