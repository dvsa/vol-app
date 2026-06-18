<?php

/**
 * Test the address service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Helper;

use Common\Service\Helper\AddressHelperService;

/**
 * Test the address service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddressHelperServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Holds the service
     *
     * @var object
     */
    private $service;

    /**
     * Setup the service
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->service = new AddressHelperService();
    }

    /**
     * Test formatPostalAddress with simple parts
     *
     * @group helper_service
     * @group address_helper_service
     */
    public function testFormatPostalAddressWithSimpleParts(): void
    {
        $address = [
            'organisation_name' => 'My Company Ltd',
            'address_line1' => '60 Burley Road',
            'address_line2' => 'Awesome House Street Name',
            'address_line3' => '',
            'address_line4' => '',
            'post_town' => 'Some Town',
            'postcode' => 'AB1 1AB',
        ];

        $expectedAddress = [
            'organisationName' => 'My Company Ltd',
            'addressLine1' => '60 Burley Road',
            'addressLine2' => 'Awesome House Street Name',
            'addressLine3' => '',
            'addressLine4' => '',
            'town' => 'Some Town',
            'postcode' => 'AB1 1AB',
            'countryCode' => 'GB'
        ];

        $addressDetails = $this->service->formatPostalAddress($address);

        $this->assertEquals($expectedAddress, $addressDetails);
    }

    /**
     * Test format addresses for select
     *
     * @group helper_service
     * @group address_helper_service
     */
    public function testFormatAddressesForSelect(): void
    {
        $list = [
            [
                'uprn' => 123,
                'organisation_name' => 'My Company Ltd',
                'address_line1' => '123 Really Awesome House Street Name',
                'address_line2' => '',
                'address_line3' => '',
                'address_line4' => '',
                'post_town' => 'Some Town',
                'postcode' => 'AB1 1AB',
            ],
            [
                'uprn' => 234,
                'organisation_name' => '',
                'address_line1' => 'My Company Ltd',
                'address_line2' => '234 Awesome House Street Name',
                'address_line3' => '',
                'address_line4' => '',
                'post_town' => 'Some Town',
                'postcode' => 'AB1 1AB',
            ],
            [
                'uprn' => 345,
                'organisation_name' => '',
                'address_line1' => 'My Company Ltd',
                'address_line2' => '345 Awesome House Street Name',
                'address_line3' => '',
                'address_line4' => '',
                'post_town' => 'Some Town',
                'postcode' => 'AB1 1AB',
            ]
        ];

        $expectedResult = [
            123 => 'My Company Ltd, 123 Really Awesome House Street N…',
            234 => 'My Company Ltd, 234 Awesome House Street Name, So…',
            345 => 'My Company Ltd, 345 Awesome House Street Name, So…',
        ];

        $result = $this->service->formatAddressesForSelect($list);

        $this->assertSame($expectedResult, $result);
    }
}
