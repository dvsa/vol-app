<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Dvsa\Olcs\Api\Entity\Types\DateType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Types\DateType::class)]
final class DateTypeTest extends MockeryTestCase
{
    /** @var  DateType */
    private $sut;
    /** @var  AbstractPlatform | m\MockInterface */
    private $mockPlatform;

    #[\Override]
    public function setUp(): void
    {
        DateType::overrideType('datetime', DateType::class);
        $this->sut = DateType::getType('datetime');

        $this->mockPlatform = m::mock(AbstractPlatform::class);
    }

    public function testConvertToPhpValueCovertExc(): void
    {
        $value = '1146711721';

        $this->expectException(
            \Doctrine\DBAL\Types\ConversionException::class
        );

        /** @var AbstractPlatform | m\MockInterface $mockPlatform */
        $this->mockPlatform->shouldReceive('getDateFormatString')->times(2)->andReturn('d-m-Y');

        $this->sut->convertToPHPValue($value, $this->mockPlatform);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestConvertToPhpValue')]
    public function testConvertToPhpValue(mixed $value, mixed $expect): void
    {
        $this->mockPlatform->shouldReceive('getDateFormatString')->atMost(1)->andReturn('d-m-Y');

        $actual = $this->sut->convertToPHPValue($value, $this->mockPlatform);

        $this->assertEquals($expect, $actual);
    }

    public static function dpTestConvertToPhpValue(): \Iterator
    {
        yield [
            'value' => null,
            'expect' => null,
        ];
        yield [
            'value' => new \DateTime('@1146711721'),
            'expect' => '2006-05-04',
        ];
        yield [
            'value' => '04-05-2016',
            'expect' => '2016-05-04',
        ];
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestConvertToDatabaseValue')]
    public function testConvertToDatabaseValue(mixed $value, mixed $expect): void
    {
        /** @var AbstractPlatform | m\MockInterface $mockPlatform */
        $mockPlatform = m::mock(AbstractPlatform::class);

        if ($value !== null) {
            $mockPlatform->shouldReceive('getDateFormatString')->once()->andReturn('d-m-Y');
        }

        $actual = $this->sut->convertToDatabaseValue($value, $mockPlatform);

        $this->assertEquals($expect, $actual);
    }

    public static function dpTestConvertToDatabaseValue(): \Iterator
    {
        yield [
            'value' => null,
            'expect' => null,
        ];
        yield [
            'value' => '@1146711721',
            'expect' => '04-05-2006',
        ];
        yield [
            'value' => new \DateTime('2013-12-11'),
            'expect' => '11-12-2013',
        ];
    }
}
