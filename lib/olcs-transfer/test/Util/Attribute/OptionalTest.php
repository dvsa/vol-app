<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Util\Attribute;

use Dvsa\Olcs\Transfer\Util\Attribute\Optional;

/**
 * Optional test
 */
final class OptionalTest extends \PHPUnit\Framework\TestCase
{
    public function testInstantiationNoValue()
    {
        $sut = new Optional();

        $this->assertTrue($sut->getOptional());
    }

    /**
     * @param  mixed $value    value passed from annotation
     * @param  bool $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('valueProvider')]
    public function testInstantiationValue(mixed $value, $expected)
    {
        $sut = new Optional($value);
        $this->assertSame($expected, $sut->getOptional());
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function valueProvider(): \Iterator
    {
        yield [
            true, true,
        ];
        yield [
            false, false,
        ];
    }
}
