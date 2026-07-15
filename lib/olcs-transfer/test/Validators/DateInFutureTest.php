<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\DateInFuture;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Class DateNotInFutureTest
 */
final class DateInFutureTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testIsValid($expected, $value, $options)
    {
        $sut = m::mock(DateInFuture::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->setOptions($options);

        if ($options['use_time']) {
            $sut->shouldReceive('getNowDateTime')->andReturn(new \DateTime('2016-06-21 15:43'));
        } else {
            $sut->shouldReceive('getNowDateTime')->andReturn(new \DateTime('2016-06-21'));
        }

        $this->assertSame($expected, $sut->isValid($value));
    }

    public static function dataProvider(): \Iterator
    {
        yield [true, '2017-01-01 10:10:10', ['use_time' => true, 'include_today' => true, 'allow_empty' => false]];
        yield [true, '2016-06-21', ['use_time' => false, 'include_today' => true, 'allow_empty' => false]];
        yield [true, '2016-06-22', ['use_time' => false, 'include_today' => true, 'allow_empty' => false]];
        yield [false, '2016-06-21', ['use_time' => false, 'include_today' => false, 'allow_empty' => false]];
        yield [false, '', ['use_time' => false, 'include_today' => true, 'allow_empty' => false]];
        yield [true, '', ['use_time' => false, 'include_today' => true, 'allow_empty' => true]];
        yield [true, '2016-06-22', ['use_time' => false, 'include_today' => true, 'allow_empty' => true]];
        yield [false, '2015-06-22', ['use_time' => false, 'include_today' => true, 'allow_empty' => false]];
    }

    public function testIsValidNoMock()
    {
        $sut = new DateInFuture();

        $this->assertTrue($sut->isValid('3015-05-20'));
        $this->assertFalse($sut->isValid('2000-05-20'));
    }
}
