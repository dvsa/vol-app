<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\ContactNumber;

/**
 * ContactDetails Test
 */
final class ContactNumberTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testFormat(mixed $input, mixed $expected): void
    {
        $this->assertEquals($expected, ContactNumber::format($input));
    }

    public static function dataProvider(): \Iterator
    {
        $primary = [
            'phoneContactType' => ['id' => PhoneContact::TYPE_PRIMARY],
            'phoneNumber' => '1111111'
        ];
        $secondary = [
            'phoneContactType' => ['id' => PhoneContact::TYPE_SECONDARY],
            'phoneNumber' => '2222222'
        ];
        yield [
            [$primary],
            $primary['phoneNumber']
        ];
        yield [
            [$secondary],
            $secondary['phoneNumber']
        ];
        yield [
            [$primary, $secondary],
            $primary['phoneNumber']
        ];
        yield [
            [$secondary, $primary],
            $primary['phoneNumber']
        ];
        yield [
            [$secondary, $primary, $secondary],
            $primary['phoneNumber']
        ];
    }
}
