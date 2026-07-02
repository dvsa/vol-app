<?php

namespace Dvsa\OlcsTest\Transfer\Util\Annotation;

use Dvsa\Olcs\Transfer\Util\Annotation\ContinueIfEmpty;

/**
 * ContinueIfEmpty test
 */
class ContinueIfEmptyTest extends \PHPUnit\Framework\TestCase
{
    public function testInstantiationNoValue()
    {
        $sut = new ContinueIfEmpty([]);

        $this->assertSame(true, $sut->getContinueIfEmpty());
    }

    /**
     * @param  mixed $value    value passed from annotation
     * @param  bool $expected
     * @dataProvider valueProvider
     */
    public function testInstantiationValue(mixed $value, $expected)
    {
        $sut = new ContinueIfEmpty(['value' => $value]);
        $this->assertSame($expected, $sut->getContinueIfEmpty());
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
                false, false, // in reality, we would just omit the annotation rather than pass false
            ],
        ];
    }
}
