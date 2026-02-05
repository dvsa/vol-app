<?php

namespace Dvsa\OlcsTest\Api\Entity\Traits;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ProcessDateTimeTraitTest
 */
class ProcessDateTimeTraitTest extends MockeryTestCase
{
    /**
     *
     * @param $expected
     * @param $date
     * @param $format
     * @param $zeroTime
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestProcessDate')]
    public function testProcessDate($expected, $date, $format, $zeroTime)
    {
        $sut = new StubProcessDateTime();
        $this->assertEquals($expected, $sut->processDate($date, $format, $zeroTime));
    }

    public static function dataProviderTestProcessDate()
    {
        return [
            [null, '2017-02-18', 'Y', true],
            [null, '2017-02-18', 'Y', false],
            [new \DateTime('2017-02-18'), '2017-02-18', 'Y-m-d', true],
            [new \DateTime('2017-02-18 12:22:33'), '2017-02-18 12:22:33', 'Y-m-d H:i:s', false],
            [new \DateTime('2017-02-18 00:00:00'), '2017-02-18 12:22:33', 'Y-m-d H:i:s', true],
            [new \DateTime('2017-02-18'), '18/02/2017', 'd/m/Y', true],
        ];
    }

    /**
     *
     * @param $expected
     * @param $date
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestProcessDateDefaults')]
    public function testProcessDateDefaults($expected, $date)
    {
        $sut = new StubProcessDateTime();
        $this->assertEquals($expected, $sut->processDate($date));
    }

    public static function dataProviderTestProcessDateDefaults()
    {
        return [
            [null, '18/02/2017'],
            [null, '2017-02-18 12:11:22'],
            [new \DateTime('2017-02-18 00:00:00'), '2017-02-18'],
        ];
    }

    /**
     *
     * @param $expected
     * @param $value
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestAsDateTime')]
    public function testAsDateTime($expected, $value)
    {
        $sut = new StubProcessDateTime();
        $this->assertEquals($expected, $sut->asDateTime($value));
    }

    public static function dataProviderTestAsDateTime()
    {
        return [
            [new \DateTime('2017-02-12'), new \DateTime('2017-02-12')],
            [null, null],
            [null, ''],
            [new \DateTime('2017-02-12 00:00:00'), '2017-02-12'],
            [new \DateTime('1997-04-31 12:22:00'), '1997-04-31 12:22'],
        ];
    }
}
