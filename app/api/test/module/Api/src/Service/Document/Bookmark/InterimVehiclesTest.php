<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Document\Bookmark\InterimVehicles;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class InterimVehiclesTest extends MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new InterimVehicles();
        $query = $bookmark->getQuery(['application' => 123]);

        $this->assertInstanceOf(QueryInterface::class, $query);
        $this->assertEquals(123, $query->getId());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRender')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = m::mock(InterimVehicles::class)->makePartial();
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
                    'interimAuthVehicles' => 10,
                    'interimAuthHgvVehicles' => 7,
                    'interimAuthLgvVehicles' => 3,
                ],
                'expected' => "7 Heavy goods vehicles\n\n3 Light goods vehicles\n\nlight_goods_vehicle.undertakings.vehicle-bookmark_translated",
            ],
            'mixed - one vehicle each' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED,
                    ],
                    'interimAuthVehicles' => 2,
                    'interimAuthHgvVehicles' => 1,
                    'interimAuthLgvVehicles' => 1,
                ],
                'expected' => "1 Heavy goods vehicles\n\n1 Light goods vehicles\n\nlight_goods_vehicle.undertakings.vehicle-bookmark_translated",
            ],
            'mixed - hgv vehicle only' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED,
                    ],
                    'interimAuthVehicles' => 1,
                    'interimAuthHgvVehicles' => 1,
                    'interimAuthLgvVehicles' => 0,
                ],
                'expected' => "1 Heavy goods vehicles\n\n0 Light goods vehicles\n\nlight_goods_vehicle.undertakings.vehicle-bookmark_translated",
            ],
            'mixed - lgv vehicle only' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED,
                    ],
                    'interimAuthVehicles' => 1,
                    'interimAuthHgvVehicles' => 0,
                    'interimAuthLgvVehicles' => 1,
                ],
                'expected' => "0 Heavy goods vehicles\n\n1 Light goods vehicles\n\nlight_goods_vehicle.undertakings.vehicle-bookmark_translated",
            ],
            'mixed - lgv not set' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED,
                    ],
                    'interimAuthVehicles' => 1,
                    'interimAuthHgvVehicles' => 1,
                    'interimAuthLgvVehicles' => null,
                ],
                'expected' => 1,
            ],
            'lgv' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_LGV,
                    ],
                    'interimAuthVehicles' => 10,
                    'interimAuthHgvVehicles' => null,
                    'interimAuthLgvVehicles' => 10,
                ],
                'expected' => "10 Light goods vehicles\n\nlight_goods_vehicle.undertakings.vehicle-bookmark_translated",
            ],
            'lgv - one vehicle' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_LGV,
                    ],
                    'interimAuthVehicles' => 1,
                    'interimAuthHgvVehicles' => null,
                    'interimAuthLgvVehicles' => 1,
                ],
                'expected' => "1 Light goods vehicles\n\nlight_goods_vehicle.undertakings.vehicle-bookmark_translated",
            ],
            'hgv' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_HGV,
                    ],
                    'interimAuthVehicles' => 10,
                    'interimAuthHgvVehicles' => 10,
                    'interimAuthLgvVehicles' => null,
                ],
                'expected' => 10,
            ],
            'psv' => [
                'data' => [
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_PSV,
                    ],
                    'interimAuthVehicles' => 10,
                    'interimAuthHgvVehicles' => 10,
                    'interimAuthLgvVehicles' => null,
                ],
                'expected' => 10,
            ],
        ];
    }
}
