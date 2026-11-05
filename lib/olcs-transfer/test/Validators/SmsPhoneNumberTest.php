<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\SmsPhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SmsPhoneNumberTest extends TestCase
{
    #[DataProvider('validProvider')]
    public function testValidNumbersNormalise(string $input, string $expected): void
    {
        $sut = new SmsPhoneNumber();

        $this->assertTrue($sut->isValid($input));
        $this->assertSame($expected, SmsPhoneNumber::normalise($input));
    }

    public static function validProvider(): \Iterator
    {
        yield 'national mobile' => ['07700 900123', '+447700900123'];
        yield 'plus 44 with (0)' => ['+44 (0)7700 900123', '+447700900123'];
        yield '0044 prefix' => ['0044 7700900123', '+447700900123'];
        yield 'dashes' => ['07700-900-123', '+447700900123'];
        yield 'london landline' => ['020 7946 0000', '+442079460000'];
        yield 'jersey' => ['07797 123456', '+447797123456'];
        // pasted from Word or a web page
        yield 'non-breaking spaces' => ["07700\u{00A0}900\u{00A0}123", '+447700900123'];
        yield 'narrow non-breaking spaces' => ["+44\u{202F}7700\u{202F}900123", '+447700900123'];
    }

    #[DataProvider('invalidProvider')]
    public function testInvalidNumbersRejected(string $input): void
    {
        $sut = new SmsPhoneNumber();

        $this->assertFalse($sut->isValid($input));
        $this->assertNull(SmsPhoneNumber::normalise($input));
        $this->assertSame(['smsPhoneInvalid' => 'contact-number-sms-invalid'], $sut->getMessages());
    }

    public static function invalidProvider(): \Iterator
    {
        yield 'premium rate' => ['0906 123 4567'];
        yield 'non UK' => ['+33 6 12 34 56 78'];
        yield 'letters' => ['07700 9OO123'];
        yield 'freephone' => ['0800 123 4567'];
        yield 'too short' => ['123'];
        yield 'french national' => ['0612345678'];
        yield 'empty' => [''];
        yield 'invalid utf-8' => ["07700\xC2900123"];
    }

    public function testNonStringRejected(): void
    {
        $this->assertFalse((new SmsPhoneNumber())->isValid(null));
        $this->assertFalse((new SmsPhoneNumber())->isValid(['07700 900123']));
    }
}
