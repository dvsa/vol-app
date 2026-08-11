<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Util\Annotation;

use Dvsa\Olcs\Transfer\Util\Annotation\DoNotExchange;

/**
 * DoNotExchange test
 */
final class DoNotExchangeTest extends \PHPUnit\Framework\TestCase
{
    public function testInstantiationNoValue()
    {
        $sut = new DoNotExchange([]);

        $this->assertTrue($sut->getDoNotExchange());
    }

    /**
     * @param  mixed $value    value passed from annotation
     * @param  bool $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('valueProvider')]
    public function testInstantiationValue(mixed $value, $expected)
    {
        $sut = new DoNotExchange(['value' => $value]);
        $this->assertSame($expected, $sut->getDoNotExchange());
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
