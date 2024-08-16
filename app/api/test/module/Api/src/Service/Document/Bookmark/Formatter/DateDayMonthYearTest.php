<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\DateDayMonthYear;

/**
 * DateDayMonthYear formatter test
 */
class DateDayMonthYearTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider scenariosProvider
     */
    public function testFormat($date, $expected)
    {
        $data = ['validFrom' => $date];

        $this->assertEquals(
            $expected,
            DateDayMonthYear::format($data)
        );
    }

    public function scenariosProvider()
    {
        return [
            ['2018-02-01 15:10:11', '01 February 2018'],
            ['2020-05-27 11:10:24', '27 May 2020'],
        ];
    }
}
