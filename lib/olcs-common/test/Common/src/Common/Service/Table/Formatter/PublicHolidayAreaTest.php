<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\PublicHolidayArea;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Service\Table\Formatter\PublicHolidayArea::class)]
final class PublicHolidayAreaTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestFormat')]
    public function testFormat($data, $expect): void
    {
        $this->assertEquals($expect, new PublicHolidayArea()->format($data));
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | string)>>
     *
     * @psalm-return list{array{data: array{isEngland: 'N', isNi: 'N'}, expect: 'none'}, array{data: array{isEngland: 'Y', isWales: 'Y', isScotland: 'Y', isNi: 'Y'}, expect: 'England, Wales, Scotland, Northern Ireland'}}
     */
    public static function dpTestFormat(): \Iterator
    {
        yield [
            'data' => [
                'isEngland' => 'N',
                'isNi' => 'N',
            ],
            'expect' => PublicHolidayArea::NO_AREA,
        ];
        yield [
            'data' => [
                'isEngland' => 'Y',
                'isWales' => 'Y',
                'isScotland' => 'Y',
                'isNi' => 'Y',
            ],
            'expect' => 'England, Wales, Scotland, Northern Ireland',
        ];
    }
}
