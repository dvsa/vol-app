<?php

declare(strict_types=1);

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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpConvertToPhpValue')]
    public function testConvertToPhpValue(mixed $value, mixed $expected): void
    {
        static::assertSame($expected, $this->sut->convertToPHPValue($value, $this->mockPlatform));
    }

    public static function dpConvertToPhpValue(): array
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

    public function testConvertToPhpValueConvertExc(): void
    {
        $value = '00000';

        $this->expectException(
            \Doctrine\DBAL\Types\ConversionException::class
        );

        $this->sut->convertToPHPValue($value, $this->mockPlatform);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpConvertToDatabaseValue')]
    public function testConvertToDatabaseValue(mixed $value, mixed $expected): void
    {
        static::assertSame($expected, $this->sut->convertToDatabaseValue($value, $this->mockPlatform));
    }

    public static function dpConvertToDatabaseValue(): array
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
