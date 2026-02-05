<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\System;

use Dvsa\Olcs\Api\Domain\CommandHandler\System\CreateFinancialStandingRate as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\FinancialStandingRate as Repo;
use Dvsa\Olcs\Api\Entity\System\FinancialStandingRate as Entity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\System\CreateFinancialStandingRate as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * CreateFinancialStandingRate command handler test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CreateFinancialStandingRateTest extends AbstractCommandHandlerTestCase
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
        $params = [
            'goodsOrPsv' => $goodsOrPsv,
            'licenceType' => $licenceType,
            'vehicleType' => $vehicleType,
            'firstVehicleRate' => '1000.01',
            'additionalVehicleRate' => '100.01',
            'effectiveFrom' => '2015-09-10',
        ];

        $command = Command::create($params);

        $newId = 69;
        $savedRate = null;
        $this->repoMap['FinancialStandingRate']
            ->shouldReceive('fetchByCategoryTypeAndDate')
            ->once()
            ->andReturn([])
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (Entity $rate) use (&$savedRate, $newId) {
                    $savedRate = $rate;
                    $savedRate->setId($newId);
                }
            );

        $response = $this->sut->handleCommand($command);

        $this->assertEquals(['financialStandingRate' => $newId], $response->getIds());
        $this->assertEquals(['Financial Standing Rate created'], $response->getMessages());

        $this->assertEquals($this->mapRefData($goodsOrPsv), $savedRate->getGoodsOrPsv());
        $this->assertEquals($this->mapRefData($licenceType), $savedRate->getLicenceType());
        $this->assertEquals($this->mapRefData($vehicleType), $savedRate->getVehicleType());
        $this->assertEquals('1000.01', $savedRate->getFirstVehicleRate());
        $this->assertEquals('100.01', $savedRate->getAdditionalVehicleRate());
        $this->assertInstanceOf(\DateTime::class, $savedRate->getEffectiveFrom());
        $this->assertEquals('2015-09-10', $savedRate->getEffectiveFrom()->format('Y-m-d'));
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
        $params = [
            'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            'vehicleType' => Entity::VEHICLE_TYPE_NOT_APPLICABLE,
            'firstVehicleRate' => '1000.01',
            'additionalVehicleRate' => '100.01',
            'effectiveFrom' => '2015-09-10',
        ];

        $command = Command::create($params);

        $this->repoMap['FinancialStandingRate']
            ->shouldReceive('fetchByCategoryTypeAndDate')
            ->once()
            ->with(
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Entity::VEHICLE_TYPE_NOT_APPLICABLE,
                '2015-09-10'
            )
            ->andReturn(
                [
                    $this->mapReference(Entity::class, 99),
                ]
            )
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

        $params = [
            'goodsOrPsv' => $goodsOrPsv,
            'licenceType' => $licenceType,
            'vehicleType' => $vehicleType,
            'firstVehicleRate' => '1000.01',
            'additionalVehicleRate' => '100.01',
            'effectiveFrom' => '2015-09-10',
        ];

        $command = Command::create($params);

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
