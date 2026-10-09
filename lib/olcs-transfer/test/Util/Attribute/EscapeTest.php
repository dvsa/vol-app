<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Util\Attribute;

use Dvsa\Olcs\Transfer\Util\Attribute\Escape;

/**
 * Escape test
 */
final class EscapeTest extends \PHPUnit\Framework\TestCase
{
    public function testInstantiationNoValue()
    {
        $sut = new Escape();

        $this->assertTrue($sut->getEscape());
    }

    /**
     * @param  mixed $value    value passed from annotation
     * @param  bool $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('valueProvider')]
    public function testInstantiationValue(mixed $value, $expected)
    {
        $sut = new Escape($value);
        $this->assertSame($expected, $sut->getEscape());
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
