<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Util\Attribute;

use Dvsa\Olcs\Transfer\Util\Attribute\ContinueIfEmpty;

/**
 * ContinueIfEmpty test
 */
final class ContinueIfEmptyTest extends \PHPUnit\Framework\TestCase
{
    public function testInstantiationNoValue()
    {
        $sut = new ContinueIfEmpty();

        $this->assertTrue($sut->getContinueIfEmpty());
    }

    /**
     * @param  mixed $value    value passed from annotation
     * @param  bool $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('valueProvider')]
    public function testInstantiationValue(mixed $value, $expected)
    {
        $sut = new ContinueIfEmpty($value);
        $this->assertSame($expected, $sut->getContinueIfEmpty());
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
            false, false, // in reality, we would just omit the annotation rather than pass false
        ];
    }
}
