<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\DateDayMonthYear;

/**
 * DateDayMonthYear formatter test
 */
class DateDayMonthYearTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('scenariosProvider')]
    public function testFormat(mixed $date, mixed $expected): void
    {
        $data = ['validFrom' => $date];

        $this->assertEquals(
            $expected,
            DateDayMonthYear::format($data)
        );
    }

    public static function scenariosProvider(): array
    {
        return [
            ['2018-02-01 15:10:11', '01 February 2018'],
            ['2020-05-27 11:10:24', '27 May 2020'],
        ];
    }
}
