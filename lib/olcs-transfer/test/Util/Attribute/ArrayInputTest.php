<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Util\Attribute;

use Dvsa\Olcs\Transfer\Util\Attribute\ArrayInput;

/**
 * ArrayInput test
 */
final class ArrayInputTest extends \PHPUnit\Framework\TestCase
{
    public function testInstantiationNoValue()
    {
        $sut = new ArrayInput();

        $this->assertTrue($sut->getArrayInput());
    }

    /**
     * @param  mixed $value    value passed from annotation
     * @param  bool $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('valueProvider')]
    public function testInstantiationValue(mixed $value, $expected)
    {
        $sut = new ArrayInput($value);
        $this->assertSame($expected, $sut->getArrayInput());
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
