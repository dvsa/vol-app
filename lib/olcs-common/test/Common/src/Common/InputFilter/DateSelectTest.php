<?php

declare(strict_types=1);

namespace CommonTest\InputFilter;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * DateSelectTest
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class DateSelectTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestGetValue')]
    public function testGetRawValue($value, $expected): void
    {
        $sut = new \Common\InputFilter\DateSelect();
        $sut->setValue($value);

        $this->assertSame($expected, $sut->getRawValue());
    }

    /**
     * @return \Iterator<(int | string), array<(array<(int | string)> | string | null)>>
     *
     * @psalm-return list{list{'foo', 'foo'}, list{null, null}, list{array{month: 2, year: 3}, array{month: 2, year: 3}}, list{array{day: 1, month: 2, year: 3}, array{day: 1, month: 2, year: 3}}, list{array{day: '', month: '', year: ''}, null}}
     */
    public static function dataProviderTestGetValue(): \Iterator
    {
        // value, expected
        yield ['foo', 'foo'];
        yield [null, null];
        yield [['month' => 2, 'year' => 3], ['month' => 2, 'year' => 3]];
        yield [['day' => 1, 'month' => 2, 'year' => 3], ['day' => 1, 'month' => 2, 'year' => 3]];
        yield [['day' => '', 'month' => '', 'year' => ''], null];
    }
}
