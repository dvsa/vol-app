<?php

namespace CommonTest\Validator;

use Common\Validator\DateInFuture;

/**
 * Class DateTimeInFutureTest
 * @package CommonTest\Validator
 */
class DateInFutureTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testIsValid($expected, $value): void
    {
        $sut = \Mockery::mock(\Common\Validator\DateInFuture::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('getNowDateTime')->andReturn(new \DateTime('2015-05-22 15:43'));

        $this->assertSame($expected, $sut->isValid($value));
    }

    /**
     * @return (bool|string)[][]
     *
     * @psalm-return array{'Year ago': list{false, '2014-05-22 15:43'}, 'Weeks ago': list{false, '2015-05-01 15:42'}, 'Minutes ago': list{false, '2015-05-22 15:42'}, Now: list{false, '2015-05-22 15:43'}, '1 minute in future': list{true, '2015-05-22 15:44'}, '1 day in future': list{true, '2015-05-23 15:43'}, 'Days intoo future': list{true, '2015-05-25 15:43'}, 'Year in future': list{true, '2016-05-22 15:43'}, 'Date in past': list{true, '2016-05-22'}, 'Date in future': list{true, '2016-05-23'}}
     */
    public function dataProvider(): array
    {
        return [
            'Year ago'           => [false, '2014-05-22 15:43'],
            'Weeks ago'          => [false, '2015-05-01 15:42'],
            'Minutes ago'        => [false, '2015-05-22 15:42'],
            'Now'                => [false, '2015-05-22 15:43'],
            '1 minute in future' => [true, '2015-05-22 15:44'],
            '1 day in future'    => [true, '2015-05-23 15:43'],
            'Days intoo future'  => [true, '2015-05-25 15:43'],
            'Year in future'     => [true, '2016-05-22 15:43'],
            'Date in past'       => [true, '2016-05-22'],
            'Date in future'     => [true, '2016-05-23'],
        ];
    }

    public function testIsValidNoMock(): void
    {
        $sut = new DateInFuture();

        $this->assertFalse($sut->isValid('2015-05-20'));
    }
}
