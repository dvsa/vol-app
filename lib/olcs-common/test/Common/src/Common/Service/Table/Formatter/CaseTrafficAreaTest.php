<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\CaseTrafficArea;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Service\Table\Formatter\CaseTrafficArea::class)]
final class CaseTrafficAreaTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestFormat')]
    public function testFormat($data, $expect): void
    {
        $sut = new CaseTrafficArea();
        $this->assertSame($expect, $sut->format($data));
    }

    /**
     * @return \Iterator<(int | string), array<(array<array<array<string>>> | string)>>
     *
     * @psalm-return array{'lic|app': array{data: array{licence: array{trafficArea: array{name: 'unit_TaName'}}}, expect: 'unit_TaName'}, tm: array{data: array<never, never>, expect: 'NA'}}
     */
    public static function dpTestFormat(): \Iterator
    {
        yield 'lic|app' => [
            'data' => [
                'licence' => [
                    'trafficArea' => [
                        'name' => 'unit_TaName',
                    ],
                ],
            ],
            'expect' => 'unit_TaName',
        ];
        yield 'tm' => [
            'data' => [
            ],
            'expect' => CaseTrafficArea::NOT_APPLICABLE,
        ];
    }
}
