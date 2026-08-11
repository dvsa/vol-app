<?php

declare(strict_types=1);

namespace CommonTest\Validator;

use Common\Validator\DateInFuture;

/**
 * Class DateTimeInFutureTest
 * @package CommonTest\Validator
 */
final class DateInFutureTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testIsValid($expected, $value): void
    {
        $sut = \Mockery::mock(\Common\Validator\DateInFuture::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('getNowDateTime')->andReturn(new \DateTime('2015-05-22 15:43'));

        $this->assertSame($expected, $sut->isValid($value));
    }

    /**
     * @return \Iterator<(int | string), array<(bool | string)>>
     *
     * @psalm-return array{'Year ago': list{false, '2014-05-22 15:43'}, 'Weeks ago': list{false, '2015-05-01 15:42'}, 'Minutes ago': list{false, '2015-05-22 15:42'}, Now: list{false, '2015-05-22 15:43'}, '1 minute in future': list{true, '2015-05-22 15:44'}, '1 day in future': list{true, '2015-05-23 15:43'}, 'Days intoo future': list{true, '2015-05-25 15:43'}, 'Year in future': list{true, '2016-05-22 15:43'}, 'Date in past': list{true, '2016-05-22'}, 'Date in future': list{true, '2016-05-23'}}
     */
    public static function dataProvider(): \Iterator
    {
        yield 'Year ago' => [false, '2014-05-22 15:43'];
        yield 'Weeks ago' => [false, '2015-05-01 15:42'];
        yield 'Minutes ago' => [false, '2015-05-22 15:42'];
        yield 'Now' => [false, '2015-05-22 15:43'];
        yield '1 minute in future' => [true, '2015-05-22 15:44'];
        yield '1 day in future' => [true, '2015-05-23 15:43'];
        yield 'Days intoo future' => [true, '2015-05-25 15:43'];
        yield 'Year in future' => [true, '2016-05-22 15:43'];
        yield 'Date in past' => [true, '2016-05-22'];
        yield 'Date in future' => [true, '2016-05-23'];
    }

    public function testIsValidNoMock(): void
    {
        $sut = new DateInFuture();

        $this->assertFalse($sut->isValid('2015-05-20'));
    }
}
