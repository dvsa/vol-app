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
    public function testFormat($data, bool $checked): void
    {
        $data['vehicle'] = ['vrm' => 'AB12 & CDE'];
        $output = new \Common\Service\Table\Formatter\InterimVehiclesCheckbox()->format($data);
        $this->assertStringContainsString('id="interim-vehicle-1" value="1" name="vehicles[id][]"', $output);
        $this->assertStringContainsString('for="interim-vehicle-1">Select vehicle AB12 &amp; CDE</label>', $output);
        $this->assertSame($checked, str_contains($output, ' checked>'));
    }

    public static function formatProvider(): \Iterator
    {
        yield [
            [
                'interimApplication' => ['id' => 2],
                'id' => 1
            ],
            true
        ];
        yield [
            [
                'interimApplication' => [],
                'id' => 1
            ],
            false
        ];
        yield [
            [
                'id' => 1
            ],
            false
        ];
    }
}
