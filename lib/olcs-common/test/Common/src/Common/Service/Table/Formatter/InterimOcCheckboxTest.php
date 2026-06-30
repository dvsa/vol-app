<?php

/**
 * Interim OC Checkbox Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Interim OC Checkbox Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InterimOcCheckboxTest extends MockeryTestCase
{
    /**
     * Test formatter
     *
     * @group interimFormatter
     * @dataProvider formatProvider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new \Common\Service\Table\Formatter\InterimOcCheckbox())->format($data));
    }

    /**
     * @return ((int|string)[]|string)[][]
     *
     * @psalm-return list{list{array{isInterim: 'Y', id: 1}, '<input type="checkbox" value="1" name="operatingCentres[id][]" checked>'}, list{array{isInterim: 'N', id: 1}, '<input type="checkbox" value="1" name="operatingCentres[id][]" >'}, list{array{id: 1}, '<input type="checkbox" value="1" name="operatingCentres[id][]" >'}}
     */
    public function formatProvider(): array
    {
        return [
            [
                [
                    'isInterim' => 'Y',
                    'id' => 1
                ],
                '<input type="checkbox" value="1" name="operatingCentres[id][]" checked>'
            ],
            [
                [
                    'isInterim' => 'N',
                    'id' => 1
                ],
                '<input type="checkbox" value="1" name="operatingCentres[id][]" >'
            ],
            [
                [
                    'id' => 1
                ],
                '<input type="checkbox" value="1" name="operatingCentres[id][]" >'
            ],
        ];
    }
}
