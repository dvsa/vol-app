<?php

/**
 * Value Helper Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Utils\Helper;

use Dvsa\Olcs\Utils\Helper\ValueHelper;

/**
 * Value Helper Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ValueHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider isOnProvider
     */
    public function testIsOn($value, $expected)
    {
        $this->assertEquals($expected, ValueHelper::isOn($value));
    }

    public function isOnProvider()
    {
        return [
            [
                'Y',
                true
            ],
            [
                true,
                true
            ],
            [
                1,
                true
            ],
            [
                '1',
                true
            ],
            [
                'N',
                false
            ],
            [
                false,
                false
            ],
            [
                0,
                false
            ],
            [
                '0',
                false
            ],
            [
                '2',
                false
            ],
            [
                'A',
                false
            ]
        ];
    }
}
