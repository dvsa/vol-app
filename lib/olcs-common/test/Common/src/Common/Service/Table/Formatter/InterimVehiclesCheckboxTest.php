<?php

/**
 * Interim Vehicles Checkbox Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Interim Vehicles Checkbox Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InterimVehiclesCheckboxTest extends MockeryTestCase
{
    /**
     * Test formatter
     *
     * @group interimFormatter
     * @dataProvider formatProvider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new \Common\Service\Table\Formatter\InterimVehiclesCheckbox())->format($data));
    }

    /**
     * @return ((int|int[])[]|string)[][]
     *
     * @psalm-return list{list{array{interimApplication: array{id: 2}, id: 1}, '<input type="checkbox" value="1" name="vehicles[id][]" checked>'}, list{array{interimApplication: array<never, never>, id: 1}, '<input type="checkbox" value="1" name="vehicles[id][]" >'}, list{array{id: 1}, '<input type="checkbox" value="1" name="vehicles[id][]" >'}}
     */
    public function formatProvider(): array
    {
        return [
            [
                [
                    'interimApplication' => ['id' => 2],
                    'id' => 1
                ],
                '<input type="checkbox" value="1" name="vehicles[id][]" checked>'
            ],
            [
                [
                    'interimApplication' => [],
                    'id' => 1
                ],
                '<input type="checkbox" value="1" name="vehicles[id][]" >'
            ],
            [
                [
                    'id' => 1
                ],
                '<input type="checkbox" value="1" name="vehicles[id][]" >'
            ],
        ];
    }
}
