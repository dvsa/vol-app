<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\DateNotInFuture as Sut;

/**
 * Class DateNotInFutureTest
 */
class DateNotInFutureTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testIsValid($expected, $value)
    {
        $sut = \Mockery::mock(Sut::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('getNowDateTime')->andReturn(new \DateTime('2015-05-22 15:43'));

        $this->assertSame($expected, $sut->isValid($value));
    }

    public function dataProvider()
    {
        return [
            'Year ago'           => [true, '2014-05-22 15:43'],
            'Weeks ago'          => [true, '2015-05-01 15:42'],
            'Minutes ago'        => [true, '2015-05-22 15:42'],
            'Now'                => [true, '2015-05-22 15:43'],
            '1 minute in future' => [false, '2015-05-22 15:44'],
            '1 day in future'    => [false, '2015-05-23 15:43'],
            'Days into future'   => [false, '2015-05-25 15:43'],
            'Year in future'     => [false, '2016-05-22 15:43'],
            'Date in past'       => [false, '2016-05-22'],
            'Date in future'     => [false, '2016-05-23'],
        ];
    }

    public function testIsValidNoMock()
    {
        $sut = new Sut();

        $this->assertFalse($sut->isValid('3015-05-20'));
        $this->assertTrue($sut->isValid('2000-05-20'));
    }
}
