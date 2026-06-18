<?php

/**
 * OcConditionsTest.php
 */

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\OcConditions;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class OcConditionsTest
 *
 * Formatter test.
 *
 * @package CommonTest\Service\Table\Formatter
 */
class OcConditionsTest extends TestCase
{
    /**
     * @dataProvider dpFormatDataProvider
     */
    public function testFormat($data, $conditions): void
    {
        $this->assertEquals((new OcConditions())->format($data), $conditions);
    }

    /**
     * @return ((int|null|string[])[][][]|int)[][]
     *
     * @psalm-return list{list{array{conditions: list{array{licence: null, conditionType: array{id: 'cdt_und'}}, array{licence: null, conditionType: array{id: 'cdt_und'}}, array{licence: 1, conditionType: array{id: 'cdt_con'}}, array{licence: 1, conditionType: array{id: 'cdt_con'}}}}, 2}}
     */
    public function dpFormatDataProvider(): array
    {
        return [
            [
                [
                    'conditions' => [
                        ['licence' => null, 'conditionType' => ['id' => RefData::TYPE_UNDERTAKING]],
                        ['licence' => null, 'conditionType' => ['id' => RefData::TYPE_UNDERTAKING]],
                        ['licence' => 1, 'conditionType' => ['id' => RefData::TYPE_CONDITION]],
                        ['licence' => 1, 'conditionType' => ['id' => RefData::TYPE_CONDITION]]
                    ]
                ],
                2
            ]
        ];
    }
}
