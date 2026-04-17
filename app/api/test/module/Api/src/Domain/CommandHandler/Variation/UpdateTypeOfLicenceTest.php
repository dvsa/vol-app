<?php

declare(strict_types=1);

/**
 * Update Type of Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateFee as CreateApplicationFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Variation\ResetVariation as ResetVariationCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\UpdateTypeOfLicence;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\RequiresVariationException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre as ApplicationOperatingCentreEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre as LicenceOperatingCentreEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\Variation\UpdateTypeOfLicence as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Update Type of Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateTypeOfLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateTypeOfLicence();
        $this->mockRepo('Application', Application::class);
        $this->mockRepo('ApplicationOperatingCentre', ApplicationOperatingCentre::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
            LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            LicenceEntity::LICENCE_TYPE_RESTRICTED,
            LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED,
            LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
            LicenceEntity::LICENCE_CATEGORY_PSV,
            RefData::APP_VEHICLE_TYPE_HGV,
            RefData::APP_VEHICLE_TYPE_PSV,
            RefData::APP_VEHICLE_TYPE_LGV,
            RefData::APP_VEHICLE_TYPE_MIXED,
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithoutChange(): void
    {
        $data = [
            'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
            'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
            'version' => 1
        ];
        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL]);
        $application->setVehicleType($this->refData[RefData::APP_VEHICLE_TYPE_HGV]);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'No updates required'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandWithChangeWhenNotAllowed')]
    public function testHandleCommandWithChangeWhenNotAllowed(
        mixed $applicationLicenceType,
        mixed $applicationVehicleType,
        mixed $commandLicenceType,
        mixed $commandVehicleType
    ): void {
        $this->expectException(ForbiddenException::class);

        $data = [
            'licenceType' => $commandLicenceType,
            'vehicleType' => $commandVehicleType,
            'version' => 1
        ];
        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setLicenceType($this->refData[$applicationLicenceType]);
        $application->setVehicleType($this->refData[$applicationVehicleType]);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::CAN_UPDATE_LICENCE_LICENCE_TYPE, $licence)
            ->andReturn(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $this->sut->handleCommand($command);
    }

    public static function dpHandleCommandWithChangeWhenNotAllowed(): array
    {
        return [
            'standard national to standard international' => [
                LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::APP_VEHICLE_TYPE_HGV,
                LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_LGV,
            ],
            'standard international lgv to standard international mixed' => [
                LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_LGV,
                LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_MIXED,
            ],
        ];
    }

    public function testHandleCommandWithSrChangeWithoutPermission(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'licenceType' => LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED,
            'version' => 1
        ];
        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL]);
        $licence->setGoodsOrPsv($this->refData[LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE]);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL]);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::CAN_UPDATE_LICENCE_LICENCE_TYPE, $licence)
            ->andReturn(true);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandWithReset')]
    public function testHandleCommandWithReset(
        mixed $existingLicenceType,
        mixed $existingVehicleType,
        mixed $applicationLicenceType,
        mixed $applicationVehicleType,
        mixed $commandData,
        mixed $expectedResetData
    ): void {
        $command = Cmd::create($commandData);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceType($this->refData[$existingLicenceType]);
        $licence->setVehicleType($this->refData[$existingVehicleType]);
        $licence->setGoodsOrPsv($this->refData[LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE]);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setLicenceType($this->refData[$applicationLicenceType]);
        $application->setVehicleType($this->refData[$applicationVehicleType]);
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::CAN_UPDATE_LICENCE_LICENCE_TYPE, $licence)
            ->andReturn(true);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $this->setupIsInternalUser(false);

        $resetResult = new Result();
        $this->expectedSideEffect(ResetVariationCommand::class, $expectedResetData, $resetResult);

        $result = $this->sut->handleCommand($command);

        $this->assertSame($resetResult, $result);
    }

    public static function dpHandleCommandWithReset(): array
    {
        return [
            'change from mixed fleet to lgv, then to goods sn, not confirmed' => [
                LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_MIXED,
                LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_LGV,
                [
                    'id' => 111,
                    'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                    'version' => 1,
                ],
                [
                    'id' => 111,
                    'confirm' => false
                ]
            ],
            'change from mixed fleet to lgv, then to goods sn, confirmed' => [
                LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_MIXED,
                LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_LGV,
                [
                    'id' => 111,
                    'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                    'version' => 1,
                    'confirm' => true
                ],
                [
                    'id' => 111,
                    'confirm' => true
                ]
            ],
            'change from goods sn to mixed fleet, then to lgv, not confirmed' => [
                LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::APP_VEHICLE_TYPE_HGV,
                LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_MIXED,
                [
                    'id' => 111,
                    'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                    'version' => 1,
                ],
                [
                    'id' => 111,
                    'confirm' => false
                ]
            ],
            'change from goods restricted to goods sn, then to mixed fleet' => [
                LicenceEntity::LICENCE_TYPE_RESTRICTED,
                RefData::APP_VEHICLE_TYPE_HGV,
                LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::APP_VEHICLE_TYPE_HGV,
                [
                    'id' => 111,
                    'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                    'version' => 1,
                ],
                [
                    'id' => 111,
                    'confirm' => false
                ]
            ],
            'change from lgv to mixed fleet, then back again, not confirmed' => [
                LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_LGV,
                LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_MIXED,
                [
                    'id' => 111,
                    'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                    'version' => 1,
                ],
                [
                    'id' => 111,
                    'confirm' => false
                ]
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(
        mixed $existingLicenceType,
        mixed $existingVehicleType,
        mixed $existingGoodsOrPsv,
        mixed $applicationLicenceType,
        mixed $applicationVehicleType,
        mixed $applicationGoodsOrPsv,
        mixed $commandData,
        mixed $isInternalUser
    ): void {
        $command = Cmd::create($commandData);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceType($this->refData[$existingLicenceType]);
        $licence->setVehicleType($this->refData[$existingVehicleType]);
        $licence->setGoodsOrPsv($this->refData[$existingGoodsOrPsv]);

        $fee = m::mock(Fee::class)
            ->shouldReceive('getId')
            ->andReturn(222)
            ->shouldReceive('isVariationFee')
            ->andReturn(true)
            ->shouldReceive('isFullyOutstanding')
            ->andReturn(true)
            ->getMock();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setLicenceType($this->refData[$applicationLicenceType]);
        $application->setVehicleType($this->refData[$applicationVehicleType]);
        $application->setGoodsOrPsv($this->refData[$applicationGoodsOrPsv]);
        $application->setFees([$fee]);
        $application->setId(111);
        $application->shouldReceive('hasAuthChanged')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::CAN_UPDATE_LICENCE_LICENCE_TYPE, $licence)
            ->andReturn(true);

        $result1 = new Result();
        $result1->addMessage('section updated');
        $sideEffectData = ['id' => 111, 'section' => 'typeOfLicence'];
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $sideEffectData, $result1);

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->shouldReceive('save')
            ->with($application)
            ->once();

        $result2 = new Result();
        $result2->addMessage('fee 222 cancelled');
        $this->expectedSideEffect(CancelFeeCmd::class, ['id' => 222], $result2);

        $result3 = new Result();
        $result3->addMessage('fee 333 created');
        $createFeeData = [
            'id' => 111,
            'feeTypeFeeType' => FeeType::FEE_TYPE_VAR,
        ];
        $this->expectedSideEffect(CreateApplicationFeeCmd::class, $createFeeData, $result3);

        $this->setupIsInternalUser($isInternalUser);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Application saved successfully',
                'section updated',
                'fee 222 cancelled',
                'fee 333 created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $expectedLicenceType = $commandData['licenceType'];

        $this->assertSame(
            $this->refData[$expectedLicenceType],
            $application->getLicenceType()
        );
    }

    public static function dpHandleCommand(): array
    {
        return [
            'change from standard national goods to mixed fleet' => [
                LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::APP_VEHICLE_TYPE_HGV,
                LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
                LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::APP_VEHICLE_TYPE_HGV,
                LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
                [
                    'id' => 111,
                    'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                    'version' => 1
                ],
                false
            ],
            'change from mixed fleet to lgv, then to goods sn, internal' => [
                LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_MIXED,
                LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
                LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_LGV,
                LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
                [
                    'id' => 111,
                    'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                    'version' => 1,
                ],
                true
            ],
            'change from restricted, then to sn, then to si, psv' => [
                LicenceEntity::LICENCE_TYPE_RESTRICTED,
                RefData::APP_VEHICLE_TYPE_PSV,
                LicenceEntity::LICENCE_CATEGORY_PSV,
                LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::APP_VEHICLE_TYPE_PSV,
                LicenceEntity::LICENCE_CATEGORY_PSV,
                [
                    'id' => 111,
                    'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                    'version' => 1,
                ],
                false
            ],
        ];
    }

    public function testHandleCommandWhenAuthHasChanged(): void
    {
        $data = [
            'id' => 111,
            'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
            'version' => 1
        ];
        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL]);
        $licence->setGoodsOrPsv($this->refData[LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE]);

        $fee = m::mock(Fee::class)
            ->shouldReceive('getId')
            ->andReturn(222)
            ->shouldReceive('isVariationFee')
            ->andReturn(true)
            ->shouldReceive('isFullyOutstanding')
            ->andReturn(true)
            ->getMock();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL]);
        $application->setGoodsOrPsv($this->refData[LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE]);
        $application->setFees([$fee]);
        $application->setId(111);
        $application->shouldReceive('hasAuthChanged')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::CAN_UPDATE_LICENCE_LICENCE_TYPE, $licence)
            ->andReturn(true);

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            ['id' => 111, 'section' => 'typeOfLicence'],
            (new Result())->addMessage('typeOfLicence section updated')
        );

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            ['id' => 111, 'section' => 'operatingCentres'],
            (new Result())->addMessage('operatingCentres section updated')
        );

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->shouldReceive('save')
            ->with($application)
            ->once();

        $result2 = new Result();
        $result2->addMessage('fee 222 cancelled');
        $this->expectedSideEffect(CancelFeeCmd::class, ['id' => 222], $result2);

        $result3 = new Result();
        $result3->addMessage('fee 333 created');
        $createFeeData = [
            'id' => 111,
            'feeTypeFeeType' => FeeType::FEE_TYPE_VAR,
        ];
        $this->expectedSideEffect(CreateApplicationFeeCmd::class, $createFeeData, $result3);

        $this->setupIsInternalUser();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Application saved successfully',
                'typeOfLicence section updated',
                'operatingCentres section updated',
                'fee 222 cancelled',
                'fee 333 created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            $application->getLicenceType()
        );
    }

    public function testHandleCommandWithOperatingCentresToBeRemoved(): void
    {
        $data = [
            'id' => 111,
            'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
            'lgvDeclarationConfirmation' => 1,
            'version' => 1,
        ];
        $command = Cmd::create($data);

        /** @var LicenceOperatingCentreEntity $loc1 */
        $loc1 = m::mock(LicenceOperatingCentreEntity::class)->makePartial();

        /** @var LicenceOperatingCentreEntity $loc2 */
        $loc2 = m::mock(LicenceOperatingCentreEntity::class)->makePartial();

        $locs = new ArrayCollection();
        $locs->add($loc1);
        $locs->add($loc2);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL]);
        $licence->setGoodsOrPsv($this->refData[LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE]);
        $licence->setOperatingCentres($locs);

        $fee = m::mock(Fee::class)
            ->shouldReceive('getId')
            ->andReturn(222)
            ->shouldReceive('isVariationFee')
            ->andReturn(true)
            ->shouldReceive('isFullyOutstanding')
            ->andReturn(true)
            ->getMock();

        /** @var ApplicationOperatingCentreEntity $aoc1 */
        $aoc1 = m::mock(ApplicationOperatingCentreEntity::class);

        /** @var ApplicationOperatingCentreEntity $aoc2 */
        $aoc2 = m::mock(ApplicationOperatingCentreEntity::class);

        $aocs = new ArrayCollection();
        $aocs->add($aoc1);
        $aocs->add($aoc2);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setLicenceType($this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL]);
        $application->setGoodsOrPsv($this->refData[LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE]);
        $application->setFees([$fee]);
        $application->setId(111);
        $application->setOperatingCentres($aocs);
        $application->shouldReceive('hasAuthChanged')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::CAN_UPDATE_LICENCE_LICENCE_TYPE, $licence)
            ->andReturn(true);

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            ['id' => 111, 'section' => 'typeOfLicence'],
            (new Result())->addMessage('typeOfLicence section updated')
        );

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->shouldReceive('save')
            ->with($application)
            ->once();

        $this->repoMap['ApplicationOperatingCentre']
            ->shouldReceive('delete')
            ->with($aoc1)
            ->once()
            ->shouldReceive('delete')
            ->with($aoc2)
            ->once()
            ->shouldReceive('save')
            ->withArgs(function ($aoc) use ($application) {
                $this->assertEquals(ApplicationOperatingCentreEntity::ACTION_DELETE, $aoc->getAction());
                $this->assertSame($application, $aoc->getApplication());
                return true;
            })
            ->times(2);

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            ['id' => 111, 'section' => 'operatingCentres'],
            (new Result())->addMessage('operatingCentres section updated')
        );

        $this->expectedSideEffect(
            CancelFeeCmd::class,
            ['id' => 222],
            (new Result())->addMessage('fee 222 cancelled')
        );

        $this->expectedSideEffect(
            CreateApplicationFeeCmd::class,
            [
                'id' => 111,
                'feeTypeFeeType' => FeeType::FEE_TYPE_VAR,
            ],
            (new Result())->addMessage('fee 333 created')
        );

        $this->setupIsInternalUser();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Application saved successfully',
                'typeOfLicence section updated',
                'operatingCentres section updated',
                'fee 222 cancelled',
                'fee 333 created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            $application->getLicenceType()
        );
    }
}
