<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\Address;

/**
 * Address formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class AddressTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('addressProvider')]
    public function testFormat(mixed $input, mixed $expected): void
    {
        $this->assertEquals(
            $expected,
            Address::format($input)
        );
    }

    public static function addressProvider(): array
    {
        return [
            [
                [
                    'addressLine1' => 'Line 1',
                    'addressLine2' => 'Line 2',
                    'addressLine3' => 'Line 3',
                    'addressLine4' => 'Line 4',
                    'town' => 'Leeds',
                    'postcode' => 'LS9 6NF'
                ],
                "Line 1\nLine 2\nLine 3\nLine 4\nLeeds\nLS9 6NF"
            ],
            [
                [
                    'addressLine1' => 'Line 1',
                    'addressLine2' => '',
                    'addressLine4' => 'Line 4',
                    'town' => 'Leeds',
                    'postcode' => 'LS9 6NF'
                ],
                "Line 1\nLine 4\nLeeds\nLS9 6NF"
            ],
            [
                [
                    'addressLine1' => 'Line 1',
                    'addressLine2' => 'Line 2',
                    'addressLine3' => 'Line 3',
                    'addressLine4' => 'Line 4',
                    'town' => 'Leeds',
                    'postcode' => 'LS9 6NF',
                    'countryCode' => [
                        'countryDesc' => 'United Kingdom'
                    ]
                ],
                "Line 1\nLine 2\nLine 3\nLine 4\nLeeds\nLS9 6NF\nUnited Kingdom"
            ],
        ];
    }
}
