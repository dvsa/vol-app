<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Util\Attribute;

use Dvsa\Olcs\Transfer\Util\Attribute\Method;

/**
 * Method test
 */
final class MethodTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param  string $value    value passed from annotation
     * @param  string $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('valueProvider')]
    public function testInstantiationValue(string $value, $expected)
    {
        $sut = new Method($value);
        $this->assertSame($expected, $sut->getMethod());
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function valueProvider(): \Iterator
    {
        yield [
            'GET', 'GET',
        ];
        yield [
            'POST', 'POST',
        ];
    }
}

