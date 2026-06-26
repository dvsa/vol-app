<?php

/**
 * Hide If Closed Radio Formatter Test
 */

namespace CommonTest\Service\Table\Formatter;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Hide If Closed Radio Formatter Test
 */
class HideIfClosedRadioTest extends MockeryTestCase
{
    /**
     * Test formatter
     *
     * @dataProvider formatProvider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new \Common\Service\Table\Formatter\HideIfClosedRadio())->format($data));
    }

    /**
     * @return ((int|string)[]|string)[][]
     *
     * @psalm-return list{list{array{closedDate: '2015-03-24', id: 1}, ''}, list{array{closedDate: '', id: 1}, '<input type="radio" value="1" name="id">'}, list{array{id: 1}, '<input type="radio" value="1" name="id">'}}
     */
    public function formatProvider(): array
    {
        return [
            [
                [
                    'closedDate' => '2015-03-24',
                    'id' => 1
                ],
                ''
            ],
            [
                [
                    'closedDate' => '',
                    'id' => 1
                ],
                '<input type="radio" value="1" name="id">'
            ],
            [
                [
                    'id' => 1
                ],
                '<input type="radio" value="1" name="id">'
            ],
        ];
    }
}
