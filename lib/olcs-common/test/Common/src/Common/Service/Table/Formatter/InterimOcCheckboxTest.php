<?php

/**
 * Interim OC Checkbox Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Interim OC Checkbox Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class InterimOcCheckboxTest extends MockeryTestCase
{
    /**
     * Test formatter
     */
    #[\PHPUnit\Framework\Attributes\Group('interimFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new \Common\Service\Table\Formatter\InterimOcCheckbox()->format($data));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(int | string)> | string)>>
     *
     * @psalm-return list{list{array{isInterim: 'Y', id: 1}, '<input type="checkbox" value="1" name="operatingCentres[id][]" checked>'}, list{array{isInterim: 'N', id: 1}, '<input type="checkbox" value="1" name="operatingCentres[id][]" >'}, list{array{id: 1}, '<input type="checkbox" value="1" name="operatingCentres[id][]" >'}}
     */
    public static function formatProvider(): \Iterator
    {
        yield [
            [
                'isInterim' => 'Y',
                'id' => 1
            ],
            '<input type="checkbox" value="1" name="operatingCentres[id][]" checked>'
        ];
        yield [
            [
                'isInterim' => 'N',
                'id' => 1
            ],
            '<input type="checkbox" value="1" name="operatingCentres[id][]" >'
        ];
        yield [
            [
                'id' => 1
            ],
            '<input type="checkbox" value="1" name="operatingCentres[id][]" >'
        ];
    }
}
