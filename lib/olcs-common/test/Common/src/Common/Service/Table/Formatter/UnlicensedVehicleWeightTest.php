<?php

/**
 * UnlicensedVehicleWeightTest.php
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\StackHelperService;
use Common\Service\Table\Formatter\UnlicensedVehicleWeight;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class UnlicensedVehicleWeightTest
 *
 * @package CommonTest\Service\Table\Formatter
 */
final class UnlicensedVehicleWeightTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testFormat($data, $expected): void
    {
        $column = [
            'title' => 'some.translation.key',
            'stack' => 'vehicle->platedWeight',
            'formatter' => UnlicensedVehicleWeight::class,
            'name' => 'weight',
        ];

        $this->assertEquals($expected, new \Common\Service\Table\Formatter\UnlicensedVehicleWeight(new StackHelperService())->format($data, $column));
    }

    /**
     * @return \Iterator<(int | string), array<(array<array<(int | null)>> | string)>>
     *
     * @psalm-return array{'empty weight': list{array{vehicle: array{platedWeight: null}}, ''}, 'weight specified': list{array{vehicle: array{platedWeight: 99}}, '99 kg'}}
     */
    public static function formatProvider(): \Iterator
    {
        yield 'empty weight' => [
            [
                'vehicle' => [
                    'platedWeight' => null,
                ],
            ],
            '',
        ];
        yield 'weight specified' => [
            [
                'vehicle' => [
                    'platedWeight' => 99,
                ],
            ],
            '99 kg',
        ];
    }
}
