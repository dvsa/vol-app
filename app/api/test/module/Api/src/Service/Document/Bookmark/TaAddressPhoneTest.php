<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Service\Document\Bookmark\TaAddressPhone;

/**
 * TA Address (with phone number) test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class TaAddressPhoneTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new TaAddressPhone();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpAllTrafficAreas')]
    public function testRenderWithNoPhone(string $trafficAreaId): void
    {
        $bookmark = new TaAddressPhone();
        $bookmark->setData(
            [
                'trafficArea' => [
                    'id' => $trafficAreaId,
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpAllTrafficAreas')]
    public function testRenderWithNoMatchingPhone(string $trafficAreaId): void
    {
        $bookmark = new TaAddressPhone();
        $bookmark->setData(
            [
                'trafficArea' => [
                    'id' => $trafficAreaId,
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

    public static function dpAllTrafficAreas(): \Iterator
    {
        yield [TrafficArea::NORTH_EASTERN_TRAFFIC_AREA_CODE];
        yield [TrafficArea::NORTH_WESTERN_TRAFFIC_AREA_CODE];
        yield [TrafficArea::WEST_MIDLANDS_TRAFFIC_AREA_CODE];
        yield [TrafficArea::EASTERN_TRAFFIC_AREA_CODE];
        yield [TrafficArea::WELSH_TRAFFIC_AREA_CODE];
        yield [TrafficArea::WESTERN_TRAFFIC_AREA_CODE];
        yield [TrafficArea::SE_MET_TRAFFIC_AREA_CODE];
        yield [TrafficArea::SCOTTISH_TRAFFIC_AREA_CODE];
        yield [TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRenderWithMatchingPhone')]
    public function testRenderWithMatchingPhone(string $trafficAreaId, string $expectedOutput): void
    {
        $bookmark = new TaAddressPhone();
        $bookmark->setData(
            [
                'trafficArea' => [
                    'id' => $trafficAreaId,
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
            $expectedOutput,
            $bookmark->render()
        );
    }

    public static function dpRenderWithMatchingPhone(): \Iterator
    {
        $withPhoneNumber = "TA Address 1\nLine 1\nLine 2\nLine 3\nLine 4\nLS2 4DD\n1234";
        $withoutPhoneNumber = "TA Address 1\nLine 1\nLine 2\nLine 3\nLine 4\nLS2 4DD";
        yield [TrafficArea::NORTH_EASTERN_TRAFFIC_AREA_CODE, $withPhoneNumber];
        yield [TrafficArea::NORTH_WESTERN_TRAFFIC_AREA_CODE, $withPhoneNumber];
        yield [TrafficArea::WEST_MIDLANDS_TRAFFIC_AREA_CODE, $withPhoneNumber];
        yield [TrafficArea::EASTERN_TRAFFIC_AREA_CODE, $withPhoneNumber];
        yield [TrafficArea::WELSH_TRAFFIC_AREA_CODE, $withPhoneNumber];
        yield [TrafficArea::WESTERN_TRAFFIC_AREA_CODE, $withPhoneNumber];
        yield [TrafficArea::SE_MET_TRAFFIC_AREA_CODE, $withPhoneNumber];
        yield [TrafficArea::SCOTTISH_TRAFFIC_AREA_CODE, $withPhoneNumber];
        yield [TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE, $withoutPhoneNumber];
    }
}
