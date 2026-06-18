<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\OcComplaints;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class OcComplaintsTest
 *
 * Formatter test.
 *
 * @package CommonTest\Service\Table\Formatter
 */
class OcComplaintsTest extends TestCase
{
    /**
     * @dataProvider dpFormatDataProvider
     */
    public function testFormat($data, $complaints): void
    {
        $this->assertEquals((new OcComplaints())->format($data), $complaints);
    }

    /**
     * @return ((int[][][]|string)[]|int)[][]
     *
     * @psalm-return list{list{array{operatingCentre: array{complaints: list{array{id: 1}, array{id: 2}, array{id: 3}}}}, 3}, list{list{'operatingCentre'}, 0}}
     */
    public function dpFormatDataProvider(): array
    {
        return [
            [
                [
                    'operatingCentre' => [
                        'complaints' => [
                            ['id' => 1],
                            ['id' => 2],
                            ['id' => 3],
                        ]
                    ]
                ],
                3
            ],
            [
                [
                    'operatingCentre'
                ],
                0
            ]
        ];
    }
}
