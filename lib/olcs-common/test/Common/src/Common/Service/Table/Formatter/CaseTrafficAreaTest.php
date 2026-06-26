<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\CaseTrafficArea;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Service\Table\Formatter\CaseTrafficArea
 */
class CaseTrafficAreaTest extends MockeryTestCase
{
    /**
     * @dataProvider  dpTestFormat
     */
    public function testFormat($data, $expect): void
    {
        $sut = new CaseTrafficArea();
        static::assertSame($expect, $sut->format($data));
    }

    /**
     * @return (string|string[][][])[][]
     *
     * @psalm-return array{'lic|app': array{data: array{licence: array{trafficArea: array{name: 'unit_TaName'}}}, expect: 'unit_TaName'}, tm: array{data: array<never, never>, expect: 'NA'}}
     */
    public function dpTestFormat(): array
    {
        return [
            'lic|app' => [
                'data' => [
                    'licence' => [
                        'trafficArea' => [
                            'name' => 'unit_TaName',
                        ],
                    ],
                ],
                'expect' => 'unit_TaName',
            ],
            'tm' => [
                'data' => [
                ],
                'expect' => CaseTrafficArea::NOT_APPLICABLE,
            ],
        ];
    }
}
