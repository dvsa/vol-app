<?php

/**
 * Interim Vehicles Checkbox Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Interim Vehicles Checkbox Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class InterimVehiclesCheckboxTest extends MockeryTestCase
{
    /**
     * Test formatter
     */
    #[\PHPUnit\Framework\Attributes\Group('interimFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new \Common\Service\Table\Formatter\InterimVehiclesCheckbox()->format($data));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<int> | int)> | string)>>
     *
     * @psalm-return list{list{array{interimApplication: array{id: 2}, id: 1}, '<input type="checkbox" value="1" name="vehicles[id][]" checked>'}, list{array{interimApplication: array<never, never>, id: 1}, '<input type="checkbox" value="1" name="vehicles[id][]" >'}, list{array{id: 1}, '<input type="checkbox" value="1" name="vehicles[id][]" >'}}
     */
    public static function formatProvider(): \Iterator
    {
        yield [
            [
                'interimApplication' => ['id' => 2],
                'id' => 1
            ],
            '<input type="checkbox" value="1" name="vehicles[id][]" checked>'
        ];
        yield [
            [
                'interimApplication' => [],
                'id' => 1
            ],
            '<input type="checkbox" value="1" name="vehicles[id][]" >'
        ];
        yield [
            [
                'id' => 1
            ],
            '<input type="checkbox" value="1" name="vehicles[id][]" >'
        ];
    }
}
