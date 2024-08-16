<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Service\Document\Bookmark\TaAddressPhone;

/**
 * TA Address (with phone number) test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TaAddressPhoneTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new TaAddressPhone();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoPhone()
    {
        $bookmark = new TaAddressPhone();
        $bookmark->setData(
            [
                'trafficArea' => [
                    'name' => 'TA Address 1',
                    'contactDetails' => [
                        'address' => [
                            'addressLine1' => 'Line 1',
                            'addressLine2' => 'Line 2',
                            'addressLine3' => 'Line 3',
                            'addressLine4' => 'Line 4',
                            'postcode' => 'LS2 4DD'
                        ],
                        'phoneContacts' => []
                    ]
                ]
            ]
        );

        $this->assertEquals(
            "TA Address 1\nLine 1\nLine 2\nLine 3\nLine 4\nLS2 4DD",
            $bookmark->render()
        );
    }

    public function testRenderWithNoMatchingPhone()
    {
        $bookmark = new TaAddressPhone();
        $bookmark->setData(
            [
                'trafficArea' => [
                    'name' => 'TA Address 1',
                    'contactDetails' => [
                        'address' => [
                            'addressLine1' => 'Line 1',
                            'addressLine2' => 'Line 2',
                            'addressLine3' => 'Line 3',
                            'addressLine4' => 'Line 4',
                            'postcode' => 'LS2 4DD'
                        ],
                        'phoneContacts' => [
                            [
                                'phoneNumber' => '1234',
                                'phoneContactType' => [
                                    'id' => 'foo'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $this->assertEquals(
            "TA Address 1\nLine 1\nLine 2\nLine 3\nLine 4\nLS2 4DD",
            $bookmark->render()
        );
    }

    public function testRenderWithMatchingPhone()
    {
        $bookmark = new TaAddressPhone();
        $bookmark->setData(
            [
                'trafficArea' => [
                    'name' => 'TA Address 1',
                    'contactDetails' => [
                        'address' => [
                            'addressLine1' => 'Line 1',
                            'addressLine2' => 'Line 2',
                            'addressLine3' => 'Line 3',
                            'addressLine4' => 'Line 4',
                            'postcode' => 'LS2 4DD'
                        ],
                        'phoneContacts' => [
                            [
                                'phoneNumber' => '1234',
                                'phoneContactType' => [
                                    'id' => PhoneContact::TYPE_PRIMARY
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $this->assertEquals(
            "TA Address 1\nLine 1\nLine 2\nLine 3\nLine 4\nLS2 4DD\n1234",
            $bookmark->render()
        );
    }
}
