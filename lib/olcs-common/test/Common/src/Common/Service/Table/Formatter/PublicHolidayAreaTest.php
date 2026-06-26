<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\PublicHolidayArea;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * @covers Common\Service\Table\Formatter\PublicHolidayArea
 */
class PublicHolidayAreaTest extends TestCase
{
    /**
     * @dataProvider dpTestFormat
     */
    public function testFormat($data, $expect): void
    {
        static::assertEquals($expect, (new PublicHolidayArea())->format($data));
    }

    /**
     * @return (string|string[])[][]
     *
     * @psalm-return list{array{data: array{isEngland: 'N', isNi: 'N'}, expect: 'none'}, array{data: array{isEngland: 'Y', isWales: 'Y', isScotland: 'Y', isNi: 'Y'}, expect: 'England, Wales, Scotland, Northern Ireland'}}
     */
    public function dpTestFormat(): array
    {
        return [
            [
                'data' => [
                    'isEngland' => 'N',
                    'isNi' => 'N',
                ],
                'expect' => PublicHolidayArea::NO_AREA,
            ],
            [
                'data' => [
                    'isEngland' => 'Y',
                    'isWales' => 'Y',
                    'isScotland' => 'Y',
                    'isNi' => 'Y',
                ],
                'expect' => 'England, Wales, Scotland, Northern Ireland',
            ],
        ];
    }
}
