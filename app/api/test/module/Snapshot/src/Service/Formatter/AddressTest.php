<?php

namespace Dvsa\OlcsTest\Snapshot\Service\Formatter;

use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Snapshot\Service\Formatter\Address;

/**
 * Address formatter test
 */
class AddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provider
     */
    public function testFormat($data, $column, $expected)
    {
        $this->assertEquals($expected, Address::format($data, $column));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        $address = new AddressEntity();
        $address->setAddressLine1('foo');
        $address->setAddressLine2('bar');
        $address->setAddressLine3('cake');
        $address->setTown('fourth');

        return [
            [
                [
                    'addressLine1' => 'foo'
                ],
                [],
                'foo'
            ],
            [
                [
                    'addressLine1' => 'foo',
                    'addressLine2' => 'bar'
                ],
                [],
                'foo'
            ],
            [
                [
                    'addressLine1' => 'foo',
                    'addressLine2' => 'bar',
                    'town' => 'cake'
                ],
                [],
                'foo, cake'
            ],
            [
                [
                    'addressLine1' => 'foo',
                    'addressLine2' => 'bar',
                    'addressLine3' => 'cake',
                    'town' => 'fourth'
                ],
                [],
                'foo, fourth'
            ],
            [
                [
                    'addressLine1' => 'foo',
                    'addressLine2' => 'bar',
                    'addressLine3' => 'cake',
                    'town' => 'fourth',
                    'countryCode' => [
                        'id' => 'GB'
                    ]
                ],
                [],
                'foo, fourth'
            ],
            [
                [
                    'addressLine1' => 'foo',
                    'addressLine2' => 'bar',
                    'addressLine3' => 'cake',
                    'countryCode' => [
                        'id' => 'GB'
                    ]
                ],
                [
                    'addressFields' => ['addressLine1', 'addressLine2']
                ],
                'foo, bar'
            ],
            [
                [
                    'addressLine1' => 'foo',
                    'addressLine2' => 'bar',
                    'addressLine3' => 'cake',
                    'countryCode' => [
                        'id' => 'GB'
                    ]
                ],
                [
                    'addressFields' => ['addressLine1', 'addressLine2', 'countryCode']
                ],
                'foo, bar, GB'
            ],
            [
                [
                    'addressLine1' => 'foo',
                    'addressLine2' => 'bar',
                    'addressLine3' => 'cake',
                    'countryCode' => [
                        'id' => 'GB'
                    ]
                ],
                [
                    'addressFields' => 'FULL'
                 ],
                'foo, bar, cake, GB'
            ],
            [
                $address,
                [],
                'foo, fourth'
            ],
        ];
    }
}
