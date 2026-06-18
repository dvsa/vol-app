<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\DateInFuture;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Class DateNotInFutureTest
 */
class DateInFutureTest extends MockeryTestCase
{
    /**
     * @dataProvider dataProvider
     */
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

    public function dataProvider()
    {
        return [
            [true, '2017-01-01 10:10:10', ['use_time' => true, 'include_today' => true, 'allow_empty' => false]],
            [true, '2016-06-21', ['use_time' => false, 'include_today' => true, 'allow_empty' => false]],
            [true, '2016-06-22', ['use_time' => false, 'include_today' => true, 'allow_empty' => false]],
            [false, '2016-06-21', ['use_time' => false, 'include_today' => false, 'allow_empty' => false]],
            [false, '', ['use_time' => false, 'include_today' => true, 'allow_empty' => false]],
            [true, '', ['use_time' => false, 'include_today' => true, 'allow_empty' => true]],
            [true, '2016-06-22', ['use_time' => false, 'include_today' => true, 'allow_empty' => true]],
            [false, '2015-06-22', ['use_time' => false, 'include_today' => true, 'allow_empty' => false]],
        ];
    }

    public function testIsValidNoMock()
    {
        $sut = new DateInFuture();

        $this->assertTrue($sut->isValid('3015-05-20'));
        $this->assertFalse($sut->isValid('2000-05-20'));
    }
}
