<?php

namespace Dvsa\OlcsTest\Transfer\Util\Annotation;

use Dvsa\Olcs\Transfer\Util\Annotation\DoNotExchange;

/**
 * DoNotExchange test
 */
class DoNotExchangeTest extends \PHPUnit\Framework\TestCase
{
    public function testInstantiationNoValue()
    {
        $sut = new DoNotExchange([]);

        $this->assertSame(true, $sut->getDoNotExchange());
    }

    /**
     * @param  mixed $value    value passed from annotation
     * @param  bool $expected
     * @dataProvider valueProvider
     */
    public function testInstantiationValue(mixed $value, $expected)
    {
        $sut = new DoNotExchange(['value' => $value]);
        $this->assertSame($expected, $sut->getDoNotExchange());
    }

    /**
     * @return array
     */
    public function valueProvider()
    {
        return [
            [
                true, true,
            ],
            [
                false, false,
            ],
        ];
    }
}
