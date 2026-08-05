<?php

/**
 * OcConditionsTest.php
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\OcUndertakings;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class  OcUndertakingsTest
 *
 * Formatter test.
 *
 * @package CommonTest\Service\Table\Formatter
 */
final class OcUndertakingsTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpFormatDataProvider')]
    public function testFormat($data, $conditions): void
    {
        $this->assertEquals(new OcUndertakings()->format($data), $conditions);
    }

    /**
     * @return \Iterator<(int | string), array<(array<array<array<(array<string> | int | null)>>> | int)>>
     *
     * @psalm-return list{list{array{undertakings: list{array{licence: 1, conditionType: array{id: 'cdt_und'}}, array{licence: 1, conditionType: array{id: 'cdt_und'}}, array{licence: null, conditionType: array{id: 'cdt_con'}}, array{licence: null, conditionType: array{id: 'cdt_con'}}}}, 2}}
     */
    public static function dpFormatDataProvider(): \Iterator
    {
        yield [
            [
                'undertakings' => [
                    ['licence' => 1, 'conditionType' => ['id' => RefData::TYPE_UNDERTAKING]],
                    ['licence' => 1, 'conditionType' => ['id' => RefData::TYPE_UNDERTAKING]],
                    ['licence' => null, 'conditionType' => ['id' => RefData::TYPE_CONDITION]],
                    ['licence' => null, 'conditionType' => ['id' => RefData::TYPE_CONDITION]]
                ]
            ],
            2
        ];
    }
}
