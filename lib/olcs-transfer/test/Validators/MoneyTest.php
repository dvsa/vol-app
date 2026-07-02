<?php

/**
 * Money Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Money;

/**
 * Money Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class MoneyTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Money();
    }

    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public function isValidProvider()
    {
        return [
            [
                'abc',
                false
            ],
            [
                'abc123',
                false
            ],
            [
                '123',
                true
            ],
            [
                123,
                true
            ],
            [
                '123.45',
                true
            ],
            [
                123.45,
                true
            ],
            [
                '123.4',
                true
            ],
            [
                123.4,
                true
            ],
            [
                '123.456',
                false
            ],
            [
                123.456,
                false
            ],
            [
                '-123.45',
                false
            ],
            [
                -123,
                false
            ],
        ];
    }

    /**
     * @dataProvider isValidNegativeProvider
     */
    public function testIsValidNegative($value, $expected)
    {
        $this->sut->setOptions(['allow_negative' => true]);
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public function isValidNegativeProvider()
    {
        return [
            [
                'abc',
                false
            ],
            [
                'abc123',
                false
            ],
            [
                '123',
                true
            ],
            [
                123,
                true
            ],
            [
                '123.45',
                true
            ],
            [
                123.45,
                true
            ],
            [
                '123.4',
                true
            ],
            [
                123.4,
                true
            ],
            [
                '123.456',
                false
            ],
            [
                123.456,
                false
            ],
            [
                '-123.45',
                true
            ],
            [
                -123,
                true
            ],
        ];
    }
}
