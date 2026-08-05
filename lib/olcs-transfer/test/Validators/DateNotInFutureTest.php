<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\DateNotInFuture as Sut;

/**
 * Class DateNotInFutureTest
 */
final class DateNotInFutureTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testIsValid($expected, $value)
    {
        $sut = \Mockery::mock(Sut::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('getNowDateTime')->andReturn(new \DateTime('2015-05-22 15:43'));

        $this->assertSame($expected, $sut->isValid($value));
    }

    public static function dataProvider(): \Iterator
    {
        yield 'Year ago' => [true, '2014-05-22 15:43'];
        yield 'Weeks ago' => [true, '2015-05-01 15:42'];
        yield 'Minutes ago' => [true, '2015-05-22 15:42'];
        yield 'Now' => [true, '2015-05-22 15:43'];
        yield '1 minute in future' => [false, '2015-05-22 15:44'];
        yield '1 day in future' => [false, '2015-05-23 15:43'];
        yield 'Days into future' => [false, '2015-05-25 15:43'];
        yield 'Year in future' => [false, '2016-05-22 15:43'];
        yield 'Date in past' => [false, '2016-05-22'];
        yield 'Date in future' => [false, '2016-05-23'];
    }

    public function testIsValidNoMock()
    {
        $sut = new Sut();

        $this->assertFalse($sut->isValid('3015-05-20'));
        $this->assertTrue($sut->isValid('2000-05-20'));
    }
}
