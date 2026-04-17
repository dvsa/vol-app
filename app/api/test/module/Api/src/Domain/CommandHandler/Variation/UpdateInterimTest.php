<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\UpdateInterim;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Variation\UpdateInterim as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class UpdateInterimTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateInterim();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpValidate')]
    public function testValidate(mixed $vehicleType, mixed $totAuthHgvVehicles, mixed $totAuthLgvVehicles, mixed $totAuthTrailers, mixed $data, mixed $expected): void
    {
        $this->expectException(ValidationException::class);

        $command = Cmd::create($data);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->shouldReceive('getVehicleType')
            ->withNoArgs()
            ->andReturn(new RefData($vehicleType))
            ->shouldReceive('getTotAuthHgvVehicles')
            ->withNoArgs()
            ->andReturn($totAuthHgvVehicles)
            ->shouldReceive('getTotAuthLgvVehicles')
            ->withNoArgs()
            ->andReturn($totAuthLgvVehicles)
            ->shouldReceive('getTotAuthTrailers')
            ->withNoArgs()
            ->andReturn($totAuthTrailers);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        try {
            $this->sut->handleCommand($command);
        } catch (ValidationException $exception) {
            $this->assertSame($expected, $exception->getMessages());
            throw $exception;
        }
    }

    public static function dpValidate(): array
    {
        return [
            'LGV without required data' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'totAuthHgvVehicles' => null,
                'totAuthLgvVehicles' => 3,
                'totAuthTrailers' => null,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                ],
                'expected' => [
                    'reason' => [
                        UpdateInterim::ERR_REQUIRED => UpdateInterim::ERR_REQUIRED,
                    ],
                ],
            ],
            'LGV with zero vehicles requested' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'totAuthHgvVehicles' => null,
                'totAuthLgvVehicles' => 3,
                'totAuthTrailers' => null,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                    'authLgvVehicles' => '0',
                ],
                'expected' => [
                    'reason' => [
                        UpdateInterim::ERR_REQUIRED => UpdateInterim::ERR_REQUIRED,
                    ],
                ],
            ],
            'LGV with authorities exceeded' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'totAuthHgvVehicles' => null,
                'totAuthLgvVehicles' => 3,
                'totAuthTrailers' => null,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                    'reason' => 'Foo',
                    'authLgvVehicles' => '11',
                ],
                'expected' => [
                    'authLgvVehicles' => [
                        UpdateInterim::ERR_LGV_VEHICLE_AUTHORITY_EXCEEDED => UpdateInterim::ERR_LGV_VEHICLE_AUTHORITY_EXCEEDED,
                    ],
                ],
            ],
            'HGV without required data' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'totAuthHgvVehicles' => 3,
                'totAuthLgvVehicles' => null,
                'totAuthTrailers' => 1,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                ],
                'expected' => [
                    'reason' => [
                        UpdateInterim::ERR_REQUIRED => UpdateInterim::ERR_REQUIRED,
                    ],
                    'authTrailers' => [
                        UpdateInterim::ERR_REQUIRED => UpdateInterim::ERR_REQUIRED,
                    ],
                ],
            ],
            'HGV with zero vehicles requested' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'totAuthHgvVehicles' => 3,
                'totAuthLgvVehicles' => null,
                'totAuthTrailers' => 1,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                    'authHgvVehicles' => '0',
                    'authTrailers' => '0',
                ],
                'expected' => [
                    'reason' => [
                        UpdateInterim::ERR_REQUIRED => UpdateInterim::ERR_REQUIRED,
                    ],
                ],
            ],
            'HGV with authorities exceeded' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'totAuthHgvVehicles' => 3,
                'totAuthLgvVehicles' => null,
                'totAuthTrailers' => 1,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                    'reason' => 'Foo',
                    'authHgvVehicles' => '10',
                    'authTrailers' => '12',
                ],
                'expected' => [
                    'authHgvVehicles' => [
                        UpdateInterim::ERR_VEHICLE_AUTHORITY_EXCEEDED => UpdateInterim::ERR_VEHICLE_AUTHORITY_EXCEEDED,
                    ],
                    'authTrailers' => [
                        UpdateInterim::ERR_TRAILER_AUTHORITY_EXCEEDED => UpdateInterim::ERR_TRAILER_AUTHORITY_EXCEEDED,
                    ],
                ],
            ],
            'Mixed fleet without required data' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'totAuthHgvVehicles' => 3,
                'totAuthLgvVehicles' => 2,
                'totAuthTrailers' => 1,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                ],
                'expected' => [
                    'reason' => [
                        UpdateInterim::ERR_REQUIRED => UpdateInterim::ERR_REQUIRED,
                    ],
                    'authTrailers' => [
                        UpdateInterim::ERR_REQUIRED => UpdateInterim::ERR_REQUIRED,
                    ],
                ],
            ],
            'Mixed fleet without required data - migration' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'totAuthHgvVehicles' => 3,
                'totAuthLgvVehicles' => null,
                'totAuthTrailers' => 1,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                ],
                'expected' => [
                    'reason' => [
                        UpdateInterim::ERR_REQUIRED => UpdateInterim::ERR_REQUIRED,
                    ],
                    'authTrailers' => [
                        UpdateInterim::ERR_REQUIRED => UpdateInterim::ERR_REQUIRED,
                    ],
                ],
            ],
            'Mixed fleet with zero vehicles requested' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'totAuthHgvVehicles' => 3,
                'totAuthLgvVehicles' => 2,
                'totAuthTrailers' => 1,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                    'authHgvVehicles' => '0',
                    'authLgvVehicles' => '0',
                    'authTrailers' => '0',
                ],
                'expected' => [
                    'reason' => [
                        UpdateInterim::ERR_REQUIRED => UpdateInterim::ERR_REQUIRED,
                    ],
                ],
            ],
            'Mixed fleet with zero vehicles requested - migration' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'totAuthHgvVehicles' => 3,
                'totAuthLgvVehicles' => null,
                'totAuthTrailers' => 1,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                    'authHgvVehicles' => '0',
                    'authTrailers' => '0',
                ],
                'expected' => [
                    'reason' => [
                        UpdateInterim::ERR_REQUIRED => UpdateInterim::ERR_REQUIRED,
                    ],
                ],
            ],
            'Mixed fleet with authorities exceeded' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'totAuthHgvVehicles' => 3,
                'totAuthLgvVehicles' => 2,
                'totAuthTrailers' => 1,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                    'reason' => 'Foo',
                    'authHgvVehicles' => '10',
                    'authLgvVehicles' => '11',
                    'authTrailers' => '12',
                ],
                'expected' => [
                    'authHgvVehicles' => [
                        UpdateInterim::ERR_HGV_VEHICLE_AUTHORITY_EXCEEDED => UpdateInterim::ERR_HGV_VEHICLE_AUTHORITY_EXCEEDED,
                    ],
                    'authLgvVehicles' => [
                        UpdateInterim::ERR_LGV_VEHICLE_AUTHORITY_EXCEEDED => UpdateInterim::ERR_LGV_VEHICLE_AUTHORITY_EXCEEDED,
                    ],
                    'authTrailers' => [
                        UpdateInterim::ERR_TRAILER_AUTHORITY_EXCEEDED => UpdateInterim::ERR_TRAILER_AUTHORITY_EXCEEDED,
                    ],
                ],
            ],
            'Mixed fleet with authorities exceeded - migration' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'totAuthHgvVehicles' => 3,
                'totAuthLgvVehicles' => null,
                'totAuthTrailers' => 1,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                    'reason' => 'Foo',
                    'authHgvVehicles' => '10',
                    'authTrailers' => '12',
                ],
                'expected' => [
                    'authHgvVehicles' => [
                        UpdateInterim::ERR_HGV_VEHICLE_AUTHORITY_EXCEEDED => UpdateInterim::ERR_HGV_VEHICLE_AUTHORITY_EXCEEDED,
                    ],
                    'authTrailers' => [
                        UpdateInterim::ERR_TRAILER_AUTHORITY_EXCEEDED => UpdateInterim::ERR_TRAILER_AUTHORITY_EXCEEDED,
                    ],
                ],
            ],
            'granted' => [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'totAuthHgvVehicles' => 3,
                'totAuthLgvVehicles' => 4,
                'totAuthTrailers' => 5,
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'requested' => 'Y',
                    'reason' => 'Foo',
                    'authHgvVehicles' => '3',
                    'authLgvVehicles' => '4',
                    'authTrailers' => '5',
                    'status' => ApplicationEntity::INTERIM_STATUS_GRANTED,
                ],
                'expected' => [
                    'interimStart' => [
                        UpdateInterim::ERR_INTERIMSTARTDATE_EMPTY => UpdateInterim::ERR_INTERIMSTARTDATE_EMPTY,
                    ],
                    'interimEnd' => [
                        UpdateInterim::ERR_INTERIMENDDATE_EMPTY => UpdateInterim::ERR_INTERIMENDDATE_EMPTY,
                    ],
                ],
            ],
        ];
    }
}
