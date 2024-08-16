<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\ContactNumber;

/**
 * ContactDetails Test
 */
class ContactNumberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testFormat($input, $expected)
    {
        $this->assertEquals($expected, ContactNumber::format($input));
    }

    public function dataProvider()
    {
        $primary = [
            'phoneContactType' => ['id' => PhoneContact::TYPE_PRIMARY],
            'phoneNumber' => '1111111'
        ];
        $secondary = [
            'phoneContactType' => ['id' => PhoneContact::TYPE_SECONDARY],
            'phoneNumber' => '2222222'
        ];

        return [
            [
                [$primary],
                $primary['phoneNumber']
            ],
            [
                [$secondary],
                $secondary['phoneNumber']
            ],
            [
                [$primary, $secondary],
                $primary['phoneNumber']
            ],
            [
                [$secondary, $primary],
                $primary['phoneNumber']
            ],
            [
                [$secondary, $primary, $secondary],
                $primary['phoneNumber']
            ],
        ];
    }
}
