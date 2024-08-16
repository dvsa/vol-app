<?php

namespace Dvsa\OlcsTest\Api\Entity\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Dvsa\Olcs\Api\Entity\Types\DateTimeType;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Types\DateTimeType
 */
class DateTimeTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DateTimeType
     */
    private $sut;
    /** @var  AbstractPlatform */
    private $mockPlatform;

    public function setUp(): void
    {
        DateTimeType::overrideType('datetime', DateTimeType::class);
        $this->sut = DateTimeType::getType('datetime');

        $this->mockPlatform = m::mock(AbstractPlatform::class);
        $this->mockPlatform->shouldReceive('getDateTimeFormatString')->andReturn('d-m-Y H:i:s');
    }

    /**
     * @dataProvider dpConvertToPhpValue
     */
    public function testConvertToPhpValue($value, $expected)
    {
        static::assertSame($expected, $this->sut->convertToPHPValue($value, $this->mockPlatform));
    }

    public function dpConvertToPhpValue()
    {
        return [
            [null, null],
            [new \DateTime('2016-06-03 15:43', new \DateTimeZone('UTC')), '2016-06-03T15:43:00+0000'],
            [new \DateTime('2016-06-03 15:43', new \DateTimeZone('Europe/London')), '2016-06-03T15:43:00+0100'],
            ['2016-06-03 13:03:55', '2016-06-03T13:03:55+0000'],
            ['2016-06-03 15:43', '2016-06-03T15:43:00+0000'],
            ['2016-12-25 15:43', '2016-12-25T15:43:00+0000'],
        ];
    }

    public function testConvertToPhpValueConvertExc()
    {
        $value = '00000';

        $this->expectException(
            \Doctrine\DBAL\Types\ConversionException::class
        );

        $this->sut->convertToPHPValue($value, $this->mockPlatform);
    }

    /**
     * @dataProvider dpConvertToDatabaseValue
     */
    public function testConvertToDatabaseValue($value, $expected)
    {
        static::assertSame($expected, $this->sut->convertToDatabaseValue($value, $this->mockPlatform));
    }

    public function dpConvertToDatabaseValue()
    {
        return [
            [null, null],
            [new \DateTime('2016-06-03 15:43', new \DateTimeZone('UTC')), '03-06-2016 15:43:00'],
            [new \DateTime('2016-06-03 15:43', new \DateTimeZone('Europe/London')), '03-06-2016 14:43:00'],
            ['2016-06-03 13:03:55+0100', '03-06-2016 12:03:55'],
            ['2016-06-03 13:03:55', '03-06-2016 13:03:55'],
        ];
    }
}
