<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Document\Bookmark\AuthorisedVehicles;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AuthorisedVehiclesTest extends MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new AuthorisedVehicles();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(QueryInterface::class, $query);
        $this->assertEquals(123, $query->getId());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRender')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = m::mock(AuthorisedVehicles::class)->makePartial();
        $bookmark->shouldReceive('translate')
            ->andReturnUsing(
                fn($text) => $text . '_translated'
            );

        $bookmark->setData($data);

        $this->assertEquals(
            $expected,
            $bookmark->render()
        );
    }

    public static function dpRender(): array
    {
        return [
            'mixed' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED,
                    ],
                    'totAuthVehicles' => 10,
                    'totAuthHgvVehicles' => 7,
                    'totAuthLgvVehicles' => 3,
                ],
                'expected' => "7 Heavy goods vehicles\n\n3 Light goods vehicles\n\nlight_goods_vehicle.undertakings.vehicle-bookmark_translated",
            ],
            'mixed - one vehicle each' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED,
                    ],
                    'totAuthVehicles' => 2,
                    'totAuthHgvVehicles' => 1,
                    'totAuthLgvVehicles' => 1,
                ],
                'expected' => "1 Heavy goods vehicles\n\n1 Light goods vehicles\n\nlight_goods_vehicle.undertakings.vehicle-bookmark_translated",
            ],
            'mixed - hgv vehicle only' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED,
                    ],
                    'totAuthVehicles' => 1,
                    'totAuthHgvVehicles' => 1,
                    'totAuthLgvVehicles' => 0,
                ],
                'expected' => "1 Heavy goods vehicles\n\n0 Light goods vehicles\n\nlight_goods_vehicle.undertakings.vehicle-bookmark_translated",
            ],
            'mixed - lgv vehicle only' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED,
                    ],
                    'totAuthVehicles' => 1,
                    'totAuthHgvVehicles' => 0,
                    'totAuthLgvVehicles' => 1,
                ],
                'expected' => "0 Heavy goods vehicles\n\n1 Light goods vehicles\n\nlight_goods_vehicle.undertakings.vehicle-bookmark_translated",
            ],
            'mixed - lgv not set' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED,
                    ],
                    'totAuthVehicles' => 1,
                    'totAuthHgvVehicles' => 1,
                    'totAuthLgvVehicles' => null,
                ],
                'expected' => 1,
            ],
            'lgv' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_LGV,
                    ],
                    'totAuthVehicles' => 10,
                    'totAuthHgvVehicles' => null,
                    'totAuthLgvVehicles' => 10,
                ],
                'expected' => "10 Light goods vehicles\n\nlight_goods_vehicle.undertakings.vehicle-bookmark_translated",
            ],
            'lgv - one vehicle' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_LGV,
                    ],
                    'totAuthVehicles' => 1,
                    'totAuthHgvVehicles' => null,
                    'totAuthLgvVehicles' => 1,
                ],
                'expected' => "1 Light goods vehicles\n\nlight_goods_vehicle.undertakings.vehicle-bookmark_translated",
            ],
            'hgv' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_HGV,
                    ],
                    'totAuthVehicles' => 10,
                    'totAuthHgvVehicles' => 10,
                    'totAuthLgvVehicles' => null,
                ],
                'expected' => 10,
            ],
            'psv' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_PSV,
                    ],
                    'totAuthVehicles' => 10,
                    'totAuthHgvVehicles' => 10,
                    'totAuthLgvVehicles' => null,
                ],
                'expected' => 10,
            ],
        ];
    }
}
