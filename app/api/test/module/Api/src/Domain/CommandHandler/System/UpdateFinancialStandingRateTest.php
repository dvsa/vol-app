<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\System;

use Dvsa\Olcs\Api\Domain\CommandHandler\System\UpdateFinancialStandingRate as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\FinancialStandingRate as Repo;
use Dvsa\Olcs\Api\Entity\System\FinancialStandingRate as Entity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\System\UpdateFinancialStandingRate as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * UpdateFinancialStandingRate command handler test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UpdateFinancialStandingRateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('FinancialStandingRate', Repo::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(mixed $goodsOrPsv, mixed $licenceType, mixed $vehicleType): void
    {
        $id = 69;
        $version = 2;

        $params = [
            'id' => $id,
            'version' => $version,
            'goodsOrPsv' => $goodsOrPsv,
            'licenceType' => $licenceType,
            'vehicleType' => $vehicleType,
            'firstVehicleRate' => '1000.01',
            'additionalVehicleRate' => '100.01',
            'effectiveFrom' => '2015-09-10',
        ];

        $command = Command::create($params);

        $mockRate = m::mock(Entity::class);

        $this->repoMap['FinancialStandingRate']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, 1, $version)
            ->andReturn($mockRate)
            ->shouldReceive('fetchByCategoryTypeAndDate')
            ->once()
            ->andReturn([])
            ->shouldReceive('save')
            ->once()
            ->with($mockRate);

        $mockRate
            ->shouldReceive('setGoodsOrPsv')
            ->once()
            ->with($this->mapRefData($goodsOrPsv))
            ->andReturnSelf()
            ->shouldReceive('setLicenceType')
            ->once()
            ->with($this->mapRefData($licenceType))
            ->andReturnSelf()
            ->shouldReceive('setVehicleType')
            ->once()
            ->with($this->mapRefData($vehicleType))
            ->andReturnSelf()
            ->shouldReceive('setFirstVehicleRate')
            ->once()
            ->with('1000.01')
            ->andReturnSelf()
            ->shouldReceive('setAdditionalVehicleRate')
            ->once()
            ->with('100.01')
            ->andReturnSelf()
            ->shouldReceive('setEffectiveFrom')
            ->once()
            ->with(
                m::on(
                    function ($arg) {
                        $this->assertInstanceOf(\DateTime::class, $arg);
                        $this->assertEquals('2015-09-10', $arg->format('Y-m-d'));
                        return true;
                    }
                )
            )
            ->andReturnSelf()
            ->shouldReceive('getId')
            ->andReturn($id);

        $response = $this->sut->handleCommand($command);

        $this->assertEquals(['financialStandingRate' => $id], $response->getIds());
        $this->assertEquals(['Financial Standing Rate updated'], $response->getMessages());
    }

    public static function dpHandleCommand(): array
    {
        return [
            [
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Entity::VEHICLE_TYPE_HGV,
            ],
            [
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Entity::VEHICLE_TYPE_LGV,
            ],
            [
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Entity::VEHICLE_TYPE_NOT_APPLICABLE,
            ],
            [
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_RESTRICTED,
                Entity::VEHICLE_TYPE_NOT_APPLICABLE,
            ],
            [
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Entity::VEHICLE_TYPE_NOT_APPLICABLE,
            ],
            [
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Entity::VEHICLE_TYPE_NOT_APPLICABLE,
            ],
            [
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_RESTRICTED,
                Entity::VEHICLE_TYPE_NOT_APPLICABLE,
            ],
        ];
    }

    public function testHandleCommandDuplicateDetected(): void
    {
        $id = 69;
        $version = 2;

        $params = [
            'id' => $id,
            'version' => $version,
            'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            'vehicleType' => Entity::VEHICLE_TYPE_NOT_APPLICABLE,
            'firstVehicleRate' => '1000.01',
            'additionalVehicleRate' => '100.01',
            'effectiveFrom' => '2015-09-10',
        ];

        $command = Command::create($params);

        $mockRate = m::mock(Entity::class)
            ->shouldReceive('getId')
            ->andReturn($id)
            ->getMock();

        $mockExisting = m::mock(Entity::class)
            ->shouldReceive('getId')
            ->andReturn(99)
            ->getMock();

        $this->repoMap['FinancialStandingRate']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, 1, $version)
            ->andReturn($mockRate)
            ->shouldReceive('fetchByCategoryTypeAndDate')
            ->once()
            ->with(
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Entity::VEHICLE_TYPE_NOT_APPLICABLE,
                '2015-09-10'
            )
            ->andReturn([$mockRate, $mockExisting])
            ->shouldReceive('save')
            ->never();

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandInputRulesViolation')]
    public function testHandleCommandInputRulesViolation(
        mixed $goodsOrPsv,
        mixed $licenceType,
        mixed $vehicleType,
        mixed $expectedMessage
    ): void {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($expectedMessage);

        $id = 69;
        $version = 2;

        $params = [
            'id' => $id,
            'version' => $version,
            'goodsOrPsv' => $goodsOrPsv,
            'licenceType' => $licenceType,
            'vehicleType' => $vehicleType,
            'firstVehicleRate' => '1000.01',
            'additionalVehicleRate' => '100.01',
            'effectiveFrom' => '2015-09-10',
        ];

        $command = Command::create($params);

        $mockRate = m::mock(Entity::class);

        $this->repoMap['FinancialStandingRate']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, 1, $version)
            ->andReturn($mockRate)
            ->shouldReceive('save')
            ->never();

        $this->sut->handleCommand($command);
    }

    public static function dpHandleCommandInputRulesViolation(): array
    {
        return [
            [
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Entity::VEHICLE_TYPE_NOT_APPLICABLE,
                'Vehicle type must be HGV or LGV for standard international goods licence',
            ],
            [
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Entity::VEHICLE_TYPE_HGV,
                'Vehicle type must be Not Applicable for licences other than standard international/goods',
            ],
            [
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Entity::VEHICLE_TYPE_LGV,
                'Vehicle type must be Not Applicable for licences other than standard international/goods',
            ],
            [
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_RESTRICTED,
                Entity::VEHICLE_TYPE_HGV,
                'Vehicle type must be Not Applicable for licences other than standard international/goods',
            ],
            [
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_RESTRICTED,
                Entity::VEHICLE_TYPE_LGV,
                'Vehicle type must be Not Applicable for licences other than standard international/goods',
            ],
            [
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Entity::VEHICLE_TYPE_HGV,
                'Vehicle type must be Not Applicable for licences other than standard international/goods',
            ],
            [
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Entity::VEHICLE_TYPE_LGV,
                'Vehicle type must be Not Applicable for licences other than standard international/goods',
            ],
            [
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Entity::VEHICLE_TYPE_HGV,
                'Vehicle type must be Not Applicable for licences other than standard international/goods',
            ],
            [
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Entity::VEHICLE_TYPE_LGV,
                'Vehicle type must be Not Applicable for licences other than standard international/goods',
            ],
            [
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_RESTRICTED,
                Entity::VEHICLE_TYPE_HGV,
                'Vehicle type must be Not Applicable for licences other than standard international/goods',
            ],
            [
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_RESTRICTED,
                Entity::VEHICLE_TYPE_LGV,
                'Vehicle type must be Not Applicable for licences other than standard international/goods',
            ],
        ];
    }
}
