<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\Address;

/**
 * Address formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class AddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider addressProvider
     */
    public function testFormat($input, $expected)
    {
        $this->assertEquals(
            $expected,
            Address::format($input)
        );
    }

    public function addressProvider()
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
