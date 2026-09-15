<?php

/**
 * Postcode validator test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Postcode;

/**
 * Postcode validator test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class PostcodeTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Postcode();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid($value, $context, $expected, $expectedErrors = [], $options = [])
    {
        $this->sut->setOptions($options);

        $this->assertEquals($expected, $this->sut->isValid($value, $context));

        $this->assertEquals($expectedErrors, $this->sut->getMessages());
    }

    public static function isValidProvider(): \Iterator
    {
        yield ['LS9 6NF', ['countryCode' => 'GB'], true];
        yield ['ls9 6nf', ['countryCode' => 'GB'], true];
        yield ['ls96NF', ['countryCode' => 'GB'], true];
        yield [' ls96NF', ['countryCode' => 'GB'], true];
        yield ['ls99 6NF', ['countryCode' => 'GB'], true];
        yield [
            'ls9336NF',
            ['countryCode' => 'GB'],
            false,
            ['invalidPostcodeFormat' => 'postcode.validation.invalidPostcodeFormat']
        ];
        yield ['W1A4AA', ['countryCode' => 'GB'], true];
        yield ['GIR 0AA', ['countryCode' => 'GB'], true];
        yield [
            'not a postcode',
            ['countryCode' => 'GB'],
            false,
            ['postcodeBadLength' => 'postcode.validation.postcodeBadLength'],
        ];
        yield ['GIR 0AA', ['countryCode' => 'GB'], true];
        yield ['L2 3SW', ['countryCode' => 'GB'], true];
        yield ['L23SW', ['countryCode' => 'GB'], true];
        yield ['f0reign', ['countryCode' => 'US'], true];
        yield [
            'f0reign too long',
            ['countryCode' => 'US'],
            false,
            ['stringLengthTooLong' => 'postcode.validation.stringLengthTooLong'],
        ];
        yield ['', ['countryCode' => 'US'], true];
        yield ['', [], true];
        yield [
            '',
            ['countryCode' => 'GB'],
            false,
            ['isEmpty' => 'postcode.validation.isEmpty'],
        ];
        yield [
            '',
            ['countryCode' => 'GB'],
            true,
            [],
            ['allow_empty' => true],
        ];
    }
}
