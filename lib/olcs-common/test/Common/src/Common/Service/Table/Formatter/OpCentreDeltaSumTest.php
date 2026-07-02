<?php

/**
 * OpCentreDeltaSumTest.php
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\OpCentreDeltaSum;

/**
 * Class SumTest
 *
 * OpCentreDelta sum test.
 *
 * @package CommonTest\Service\Table\Formatter
 */
class OpCentreDeltaSumTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dpFormatDataProvider
     */
    public function testFormat($data, $expected): void
    {
        $column = [
            'name' => 'colName'
        ];

        $this->assertEquals((new OpCentreDeltaSum())->format($data, $column), $expected);
    }

    /**
     * @return ((int|string)[][]|int)[][]
     *
     * @psalm-return list{list{list{array{action: 'U', colName: 1}, array{action: 'E', colName: 3}, array{action: 'A', colName: 4}, array{action: 'C', colName: 100}, array{action: 'D', colName: 100}}, 8}, list{list{array{action: 'C', colName: 100}, array{action: 'D', colName: 100}}, 0}, list{list{array{action: 'E', colName: 3}, array{action: 'A', colName: 4}}, 7}, list{list{array{action: 'A', colName: 4}}, 4}}
     */
    public function dpFormatDataProvider(): array
    {
        return [
            [
                [
                    ['action' => 'U', 'colName' => 1],
                    ['action' => 'E', 'colName' => 3],
                    ['action' => 'A', 'colName' => 4],
                    ['action' => 'C', 'colName' => 100],
                    ['action' => 'D', 'colName' => 100]
                ],
                8
            ],
            [
                [
                    ['action' => 'C', 'colName' => 100],
                    ['action' => 'D', 'colName' => 100]
                ],
                0
            ],
            [
                [
                    ['action' => 'E', 'colName' => 3],
                    ['action' => 'A', 'colName' => 4],
                ],
                7
            ],
            [
                [
                    ['action' => 'A', 'colName' => 4],
                ],
                4
            ]
        ];
    }
}
