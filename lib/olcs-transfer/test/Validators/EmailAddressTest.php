<?php

/**
 * EmailAddress validator test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\EmailAddress;

/**
 * EmailAddress validator test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EmailAddressTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new EmailAddress();
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
                #0
                '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890@' .
                '123456789012345678901234567890123456789012345678901234567890.com',
                true
            ],
            [
                #1
                'valid@email.com',
                true
            ],
            [
                #2
                // total length greater than 254
                '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890@' .
                '123456789012345678901234567890123456789012345678901234567890.' .
                '123456789012345678901234567890123456789012345678901234567890.' .
                '123456789012345678901234567890123456789012345678901234567890.com',
                false
            ],
            [
                #3
                // domain parts max greater than 63 chars
                '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890' .
                '@1234567890123456789012345678901234567890123456789012345678901234.com',
                false
            ],
            [
                #4
                '1234567890123456789012345678901234567890123456789012345678901',
                false
            ],
            [
                #5
                // custom additional valid Top Level Domain
                '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890@' .
                '123456789012345678901234567890123456789012345678901234567890.ltd',
                true
            ],
            [
                #6
                // custom additional valid Top Level Domain
                'valid@email.ltd',
                true
            ],
            [
                #7
                // custom additional valid Top Level Domain
                // total length greater than 254
                '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890@' .
                '123456789012345678901234567890123456789012345678901234567890.' .
                '123456789012345678901234567890123456789012345678901234567890.' .
                '123456789012345678901234567890123456789012345678901234567890.ltd',
                false
            ],
            [
                #8
                // custom additional valid Top Level Domain
                // domain parts max greater than 63 chars
                '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890' .
                '@1234567890123456789012345678901234567890123456789012345678901234.ltd',
                false
            ],
            [
                #9
                // invalid Top Level Domain
                'valid@email.ppp',
                false
            ],
        ];
    }
}
