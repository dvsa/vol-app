<?php

/**
 * Postcode validator test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Postcode;

/**
 * Postcode validator test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PostcodeTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Postcode();
    }

    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid($value, $context, $expected, $expectedErrors = [], $options = [])
    {
        $this->sut->setOptions($options);

        $this->assertEquals($expected, $this->sut->isValid($value, $context));

        $this->assertEquals($expectedErrors, $this->sut->getMessages());
    }

    public function isValidProvider()
    {
        return [
            ['LS9 6NF', ['countryCode' => 'GB'], true],
            ['ls9 6nf', ['countryCode' => 'GB'], true],
            ['ls96NF', ['countryCode' => 'GB'], true],
            [' ls96NF', ['countryCode' => 'GB'], true],
            ['ls99 6NF', ['countryCode' => 'GB'], true],
            [
                'ls9336NF',
                ['countryCode' => 'GB'],
                false,
                ['invalidPostcodeFormat' => 'postcode.validation.invalidPostcodeFormat']
            ],
            ['W1A4AA', ['countryCode' => 'GB'], true],
            ['GIR 0AA', ['countryCode' => 'GB'], true],
            [
                'not a postcode',
                ['countryCode' => 'GB'],
                false,
                ['postcodeBadLength' => 'postcode.validation.postcodeBadLength'],
            ],
            ['GIR 0AA', ['countryCode' => 'GB'], true],
            ['L2 3SW', ['countryCode' => 'GB'], true],

            ['L23SW', ['countryCode' => 'GB'], true],

            ['f0reign', ['countryCode' => 'US'], true],
            [
                'f0reign too long',
                ['countryCode' => 'US'],
                false,
                ['stringLengthTooLong' => 'postcode.validation.stringLengthTooLong'],
            ],
            ['', ['countryCode' => 'US'], true],
            ['', [], true],
            [
                '',
                ['countryCode' => 'GB'],
                false,
                ['isEmpty' => 'postcode.validation.isEmpty'],
            ],
            [
                '',
                ['countryCode' => 'GB'],
                true,
                [],
                ['allow_empty' => true],
            ],

        ];
    }
}
