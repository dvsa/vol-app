<?php

declare(strict_types=1);

/**
 * Update Type Of Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateApplicationFee as CreateApplicationFeeCommand;
use Dvsa\Olcs\Api\Domain\Command\Application\GenerateLicenceNumber as GenerateLicenceNumberCommand;
use Dvsa\Olcs\Api\Domain\Command\Application\ResetApplication as ResetApplicationCommand;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCommand;
use Dvsa\Olcs\Api\Domain\Command\Licence\CancelLicenceFees;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateTypeOfLicence;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\UpdateTypeOfLicence as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update Type Of Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateTypeOfLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateTypeOfLicence();
        $this->mockRepo('Application', Application::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Licence::LICENCE_CATEGORY_PSV,
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            Licence::LICENCE_TYPE_RESTRICTED,
            Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
            RefData::APP_VEHICLE_TYPE_LGV,
            RefData::APP_VEHICLE_TYPE_MIXED,
            RefData::APP_VEHICLE_TYPE_HGV,
            RefData::APP_VEHICLE_TYPE_PSV,
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithoutChanges(): void
    {
        // Params
        $command = self::getCommand(
            'Y',
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE
        );

        // Mocks
        $application = $this->buildApplication(
            'Y',
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE
        );

        // Expectations
        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        // Assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ['No updates required']
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('requireReset')]
    public function testHandleCommandWithReset(mixed $command, mixed $applicationData, mixed $resetData): void
    {
        // Calling buildApplication to use $this->mapRefData() so RefData objects
        // match those from getRefdataReference (identity comparison)
        $application = call_user_func_array(
            $this->buildApplication(...),
            $applicationData
        );

        // Expectations
        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $resetResult = new Result();
        $this->expectedSideEffect(ResetApplicationCommand::class, $resetData, $resetResult);

        // Assertions
        $result = $this->sut->handleCommand($command);

        $this->assertSame($resetResult, $result);
    }

    public function testHandleCommandFirstTime(): void
    {
        // Params
        $command = self::getCommand('Y', Licence::LICENCE_TYPE_STANDARD_NATIONAL, Licence::LICENCE_CATEGORY_PSV);

        // Mocks
        $application = self::getApplication(null, null, null);

        // Expectations
        $application->shouldReceive('updateTypeOfLicence')
            ->with(
                'Y',
                $this->mapRefData(Licence::LICENCE_CATEGORY_PSV),
                $this->mapRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL)
            );

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result1 = new Result();
        $result1->addId('fee', 222);
        $this->expectedSideEffect(
            CreateApplicationFeeCommand::class,
            ['id' => 111, 'feeTypeFeeType' => null, 'description' => null],
            $result1
        );

        $result2 = new Result();
        $result2->addId('licNo', 333);
        $this->expectedSideEffect(GenerateLicenceNumberCommand::class, ['id' => 111], $result2);

        $result3 = new Result();
        $result3->addMessage('section1 updated');
        $result3->addMessage('section2 updated');
        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            ['id' => 111, 'section' => 'typeOfLicence'],
            $result3
        );

        // Assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 222,
                'licNo' => 333
            ],
            'messages' => [
                'section1 updated',
                'section2 updated',
                'Application saved successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithAllowedUpdate(): void
    {
        // Params
        $command = self::getCommand(
            'Y',
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            null,
            RefData::APP_VEHICLE_TYPE_HGV
        );

        $application = self::getApplication(
            'Y',
            Licence::LICENCE_TYPE_RESTRICTED,
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE
        );

        // Expectations
        $application->shouldReceive('updateTypeOfLicence')
            ->once()
            ->with(
                'Y',
                $this->mapRefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE),
                $this->mapRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL),
                $this->mapRefData(RefData::APP_VEHICLE_TYPE_HGV),
                0
            )
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock(Licence::class)
                    ->shouldReceive('getId')
                    ->andReturn(222)
                    ->getMock()
            );

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result1 = new Result();
        $result1->addMessage('5 fee(s) cancelled');
        $this->expectedSideEffect(CancelLicenceFees::class, ['id' => 222], $result1);

        $result2 = new Result();
        $result2->addId('fee', 222);
        $this->expectedSideEffect(
            CreateApplicationFeeCommand::class,
            ['id' => 111, 'feeTypeFeeType' => null, 'description' => null],
            $result2
        );

        $result3 = new Result();
        $result3->addMessage('section1 updated');
        $result3->addMessage('section2 updated');
        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            ['id' => 111, 'section' => 'typeOfLicence'],
            $result3
        );

        // Assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 222
            ],
            'messages' => [
                '5 fee(s) cancelled',
                'section1 updated',
                'section2 updated',
                'Application saved successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithPartPaidApplicationFee(): void
    {
        // Params
        $command = self::getCommand('N', Licence::LICENCE_TYPE_STANDARD_NATIONAL, Licence::LICENCE_CATEGORY_PSV);

        $application = self::getApplication(
            'N',
            Licence::LICENCE_TYPE_RESTRICTED,
            Licence::LICENCE_CATEGORY_PSV
        );

        // Expectations
        $application->shouldReceive('updateTypeOfLicence')
            ->once()
            ->with(
                'N',
                $this->mapRefData(Licence::LICENCE_CATEGORY_PSV),
                $this->mapRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL),
                $this->mapRefData(RefData::APP_VEHICLE_TYPE_PSV),
                0
            )
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock(Licence::class)
                ->shouldReceive('getId')
                ->andReturn(222)
                ->getMock()
            );

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $appFee = m::mock(FeeEntity::class);
        $appFee
            ->shouldReceive('getFeeType->getFeeType->getId')
            ->andReturn(FeeTypeEntity::FEE_TYPE_APP);
        $appFee
            ->shouldReceive('isNewApplicationFee')
            ->andReturn(true)
            ->shouldReceive('isPaid')
            ->andReturn(false)
            ->shouldReceive('isPartPaid')
            ->andReturn(true);
        $application->setFees(new ArrayCollection([$appFee]));

        $result = new Result();
        $result->addMessage('section1 updated');
        $result->addMessage('section2 updated');
        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            ['id' => 111, 'section' => 'typeOfLicence'],
            $result
        );

        // Assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'section1 updated',
                'section2 updated',
                'Application saved successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function requireReset(): array
    {
        return [
            'niFlag changed' => [
                self::getCommand(
                    'Y',
                    Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE
                ),
                [
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    Licence::LICENCE_CATEGORY_PSV
                ],
                [
                    'id' => 111,
                    'niFlag' => 'Y',
                    'operatorType' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                    'confirm' => false
                ],
            ],
            'operatorType changed' => [
                self::getCommand(
                    'Y',
                    Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    null,
                    true
                ),
                [
                    'Y',
                    Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    Licence::LICENCE_CATEGORY_PSV
                ],
                [
                    'id' => 111,
                    'niFlag' => 'Y',
                    'operatorType' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                    'confirm' => true
                ]
            ],
            'to SR' => [
                self::getCommand('N', Licence::LICENCE_TYPE_SPECIAL_RESTRICTED, Licence::LICENCE_CATEGORY_PSV),
                [
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    Licence::LICENCE_CATEGORY_PSV
                ],
                [
                    'id' => 111,
                    'niFlag' => 'N',
                    'operatorType' => Licence::LICENCE_CATEGORY_PSV,
                    'licenceType' => Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                    'confirm' => false
                ]
            ],
            'from SR' => [
                self::getCommand('N', Licence::LICENCE_TYPE_STANDARD_NATIONAL, Licence::LICENCE_CATEGORY_PSV),
                [
                    'N',
                    Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
                    Licence::LICENCE_CATEGORY_PSV
                ],
                [
                    'id' => 111,
                    'niFlag' => 'N',
                    'operatorType' => Licence::LICENCE_CATEGORY_PSV,
                    'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                    'confirm' => false
                ]
            ],
            'from LGV to mixed' => [
                self::getCommand(
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    RefData::APP_VEHICLE_TYPE_LGV
                ),
                [
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    RefData::APP_VEHICLE_TYPE_MIXED
                ],
                [
                    'id' => 111,
                    'niFlag' => 'N',
                    'operatorType' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                    'confirm' => false
                ]
            ],
            'from mixed to LGV' => [
                self::getCommand(
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    RefData::APP_VEHICLE_TYPE_MIXED
                ),
                [
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    RefData::APP_VEHICLE_TYPE_LGV
                ],
                [
                    'id' => 111,
                    'niFlag' => 'N',
                    'operatorType' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                    'confirm' => false
                ]
            ],
            'from standard international lgv to standard national' => [
                self::getCommand(
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    null
                ),
                [
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    RefData::APP_VEHICLE_TYPE_LGV
                ],
                [
                    'id' => 111,
                    'niFlag' => 'N',
                    'operatorType' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                    'confirm' => false
                ]
            ],
            'from standard international mixed to standard national' => [
                self::getCommand(
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    null
                ),
                [
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    RefData::APP_VEHICLE_TYPE_MIXED
                ],
                [
                    'id' => 111,
                    'niFlag' => 'N',
                    'operatorType' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                    'confirm' => false
                ]
            ],
            'from standard national to standard international lgv' => [
                self::getCommand(
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    RefData::APP_VEHICLE_TYPE_LGV
                ),
                [
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    RefData::APP_VEHICLE_TYPE_HGV
                ],
                [
                    'id' => 111,
                    'niFlag' => 'N',
                    'operatorType' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                    'confirm' => false
                ]
            ],
            'from standard national to standard international mixed' => [
                self::getCommand(
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    RefData::APP_VEHICLE_TYPE_MIXED
                ),
                [
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    RefData::APP_VEHICLE_TYPE_HGV
                ],
                [
                    'id' => 111,
                    'niFlag' => 'N',
                    'operatorType' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                    'confirm' => false
                ]
            ],
        ];
    }

    protected static function getCommand(
        mixed $niFlag,
        mixed $licenceType,
        mixed $operatorType = null,
        mixed $vehicleType = null,
        bool $confirm = false,
        mixed $lgvDeclarationConfirmation = null
    ): Cmd {
        $data = [
            'id' => 111,
            'version' => 1,
            'niFlag' => $niFlag,
            'operatorType' => $operatorType,
            'licenceType' => $licenceType,
            'vehicleType' => $vehicleType,
            'confirm' => $confirm,
            'lgvDeclarationConfirmation' => $lgvDeclarationConfirmation
        ];

        return Cmd::create($data);
    }

    protected static function createRefDataMock(mixed $key): ?m\MockInterface
    {
        if ($key === null) {
            return null;
        }

        return m::mock(RefData::class)->makePartial()->setId($key);
    }

    protected static function getApplication(mixed $niFlag, mixed $licenceType, mixed $operatorType, mixed $vehicleType = null): mixed
    {
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setNiFlag($niFlag);
        $application->setLicenceType(self::createRefDataMock($licenceType));
        $application->setGoodsOrPsv(self::createRefDataMock($operatorType));
        $application->setVehicleType(self::createRefDataMock($vehicleType));
        $application->setFees([]);

        return $application;
    }

    /**
     * Build an application using mapRefData so RefData objects match those from getRefdataReference.
     * Use this in test methods (not data providers) where identity comparison matters.
     */
    private function buildApplication(mixed $niFlag, mixed $licenceType, mixed $operatorType, mixed $vehicleType = null): mixed
    {
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setNiFlag($niFlag);
        $application->setLicenceType($licenceType ? $this->mapRefData($licenceType) : null);
        $application->setGoodsOrPsv($operatorType ? $this->mapRefData($operatorType) : null);
        $application->setVehicleType($vehicleType ? $this->mapRefData($vehicleType) : null);
        $application->setFees([]);

        return $application;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandWithAllowedUpdateGb')]
    public function testHandleCommandWithAllowedUpdateGb(
        mixed $command,
        mixed $applicationData,
        mixed $expectedNiFlag,
        mixed $expectedGoodsOrPsv,
        mixed $expectedLicenceType,
        mixed $expectedVehicleType,
        mixed $expectedLgvDeclarationConfirmation
    ): void {
        // Calling buildApplication to use $this->mapRefData() so RefData objects
        // match those from getRefdataReference (identity comparison)
        $application = call_user_func_array(
            $this->buildApplication(...),
            $applicationData
        );

        // Expectations
        $application->shouldReceive('updateTypeOfLicence')
            ->once()
            ->with(
                $expectedNiFlag,
                $this->mapRefData($expectedGoodsOrPsv),
                $this->mapRefData($expectedLicenceType),
                $this->mapRefData($expectedVehicleType),
                $expectedLgvDeclarationConfirmation
            )
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock(Licence::class)
                    ->shouldReceive('getId')
                    ->andReturn(222)
                    ->getMock()
            );

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result1 = new Result();
        $result1->addMessage('5 fee(s) cancelled');
        $this->expectedSideEffect(CancelLicenceFees::class, ['id' => 222], $result1);

        $result2 = new Result();
        $result2->addId('fee', 222);
        $this->expectedSideEffect(
            CreateApplicationFeeCommand::class,
            ['id' => 111, 'feeTypeFeeType' => null, 'description' => null],
            $result2
        );

        $result3 = new Result();
        $result3->addMessage('section1 updated');
        $result3->addMessage('section2 updated');
        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            ['id' => 111, 'section' => 'typeOfLicence'],
            $result3
        );

        // Assertions
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 222
            ],
            'messages' => [
                '5 fee(s) cancelled',
                'section1 updated',
                'section2 updated',
                'Application saved successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dpHandleCommandWithAllowedUpdateGb(): array
    {
        return [
            'goods standard international' => [
                self::getCommand(
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    Licence::LICENCE_CATEGORY_PSV,
                    null,
                    false,
                    0
                ),
                [
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    Licence::LICENCE_CATEGORY_PSV
                ],
                'N',
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_PSV,
                0
            ],
            'derive goods vehicle type for non standard international' => [
                self::getCommand(
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                    null,
                    false,
                    0
                ),
                [
                    'N',
                    Licence::LICENCE_TYPE_RESTRICTED,
                    Licence::LICENCE_CATEGORY_GOODS_VEHICLE
                ],
                'N',
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::APP_VEHICLE_TYPE_HGV,
                0
            ],
            'derive goods vehicle type for non standard international' => [
                self::getCommand(
                    'N',
                    Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    Licence::LICENCE_CATEGORY_PSV,
                    null,
                    false,
                    0
                ),
                [
                    'N',
                    Licence::LICENCE_TYPE_RESTRICTED,
                    Licence::LICENCE_CATEGORY_PSV
                ],
                'N',
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::APP_VEHICLE_TYPE_PSV,
                0
            ],
        ];
    }
}
