<?php

/**
 * Money Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Money;

/**
 * Money Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class MoneyTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Money();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public static function isValidProvider(): \Iterator
    {
        yield [
            'abc',
            false
        ];
        yield [
            'abc123',
            false
        ];
        yield [
            '123',
            true
        ];
        yield [
            123,
            true
        ];
        yield [
            '123.45',
            true
        ];
        yield [
            123.45,
            true
        ];
        yield [
            '123.4',
            true
        ];
        yield [
            123.4,
            true
        ];
        yield [
            '123.456',
            false
        ];
        yield [
            123.456,
            false
        ];
        yield [
            '-123.45',
            false
        ];
        yield [
            -123,
            false
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidNegativeProvider')]
    public function testIsValidNegative($value, $expected)
    {
        $this->sut->setOptions(['allow_negative' => true]);
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public static function isValidNegativeProvider(): \Iterator
    {
        yield [
            'abc',
            false
        ];
        yield [
            'abc123',
            false
        ];
        yield [
            '123',
            true
        ];
        yield [
            123,
            true
        ];
        yield [
            '123.45',
            true
        ];
        yield [
            123.45,
            true
        ];
        yield [
            '123.4',
            true
        ];
        yield [
            123.4,
            true
        ];
        yield [
            '123.456',
            false
        ];
        yield [
            123.456,
            false
        ];
        yield [
            '-123.45',
            true
        ];
        yield [
            -123,
            true
        ];
    }
}
