<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter;

/**
 * Time formatter test
 */
class TimeTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('nameProvider')]
    public function testFormat(mixed $input, mixed $expected): void
    {
        $this->assertEquals(
            $expected,
            Formatter\Time::format((array)$input)
        );
    }

    public static function nameProvider(): array
    {
        return [
            ['XX', null],
            ['2017-01-10T12:45:22+00:00', '12:45'],
            ['2017-06-10T12:45:22+00:00', '13:45'],
        ];
    }
}
