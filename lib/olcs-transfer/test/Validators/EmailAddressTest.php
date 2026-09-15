<?php

/**
 * EmailAddress validator test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\EmailAddress;

/**
 * EmailAddress validator test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class EmailAddressTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new EmailAddress();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public static function isValidProvider(): \Iterator
    {
        yield [
            #0
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890@' .
            '123456789012345678901234567890123456789012345678901234567890.com',
            true
        ];
        yield [
            #1
            'valid@email.com',
            true
        ];
        yield [
            #2
            // total length greater than 254
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890@' .
            '123456789012345678901234567890123456789012345678901234567890.' .
            '123456789012345678901234567890123456789012345678901234567890.' .
            '123456789012345678901234567890123456789012345678901234567890.com',
            false
        ];
        yield [
            #3
            // domain parts max greater than 63 chars
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890' .
            '@1234567890123456789012345678901234567890123456789012345678901234.com',
            false
        ];
        yield [
            #4
            '1234567890123456789012345678901234567890123456789012345678901',
            false
        ];
        yield [
            #5
            // custom additional valid Top Level Domain
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890@' .
            '123456789012345678901234567890123456789012345678901234567890.ltd',
            true
        ];
        yield [
            #6
            // custom additional valid Top Level Domain
            'valid@email.ltd',
            true
        ];
        yield [
            #7
            // custom additional valid Top Level Domain
            // total length greater than 254
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890@' .
            '123456789012345678901234567890123456789012345678901234567890.' .
            '123456789012345678901234567890123456789012345678901234567890.' .
            '123456789012345678901234567890123456789012345678901234567890.ltd',
            false
        ];
        yield [
            #8
            // custom additional valid Top Level Domain
            // domain parts max greater than 63 chars
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890' .
            '@1234567890123456789012345678901234567890123456789012345678901234.ltd',
            false
        ];
        yield [
            #9
            // invalid Top Level Domain
            'valid@email.ppp',
            false
        ];
    }
}
