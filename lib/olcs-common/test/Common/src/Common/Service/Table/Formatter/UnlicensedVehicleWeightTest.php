<?php

/**
 * UnlicensedVehicleWeightTest.php
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\StackHelperService;
use Common\Service\Table\Formatter\UnlicensedVehicleWeight;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class UnlicensedVehicleWeightTest
 *
 * @package CommonTest\Service\Table\Formatter
 */
class UnlicensedVehicleWeightTest extends TestCase
{
    /**
     * @dataProvider formatProvider
     */
    public function testFormat($data, $expected): void
    {
        $column = [
            'title' => 'some.translation.key',
            'stack' => 'vehicle->platedWeight',
            'formatter' => UnlicensedVehicleWeight::class,
            'name' => 'weight',
        ];

        $this->assertEquals($expected, (new \Common\Service\Table\Formatter\UnlicensedVehicleWeight(new StackHelperService()))->format($data, $column));
    }

    /**
     * @return ((int|null)[][]|string)[][]
     *
     * @psalm-return array{'empty weight': list{array{vehicle: array{platedWeight: null}}, ''}, 'weight specified': list{array{vehicle: array{platedWeight: 99}}, '99 kg'}}
     */
    public function formatProvider(): array
    {
        return [
            'empty weight' => [
                [
                    'vehicle' => [
                        'platedWeight' => null,
                    ],
                ],
                '',
            ],
            'weight specified' => [
                [
                    'vehicle' => [
                        'platedWeight' => 99,
                    ],
                ],
                '99 kg',
            ],
        ];
    }
}
