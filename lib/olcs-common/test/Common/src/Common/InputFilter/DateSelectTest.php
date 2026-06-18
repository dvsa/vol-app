<?php

namespace CommonTest\InputFilter;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * DateSelectTest
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DateSelectTest extends TestCase
{
    /**
     * @dataProvider dataProviderTestGetValue
     */
    public function testGetRawValue($value, $expected): void
    {
        $sut = new \Common\InputFilter\DateSelect();
        $sut->setValue($value);

        $this->assertSame($expected, $sut->getRawValue());
    }

    /**
     * @return ((int|string)[]|null|string)[][]
     *
     * @psalm-return list{list{'foo', 'foo'}, list{null, null}, list{array{month: 2, year: 3}, array{month: 2, year: 3}}, list{array{day: 1, month: 2, year: 3}, array{day: 1, month: 2, year: 3}}, list{array{day: '', month: '', year: ''}, null}}
     */
    public function dataProviderTestGetValue(): array
    {
        return [
            // value, expected
            ['foo', 'foo'],
            [null, null],
            [['month' => 2, 'year' => 3], ['month' => 2, 'year' => 3]],
            [['day' => 1, 'month' => 2, 'year' => 3], ['day' => 1, 'month' => 2, 'year' => 3]],
            [['day' => '', 'month' => '', 'year' => ''], null],
        ];
    }
}
