<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\ConditionUndertaking\CreateLightGoodsVehicleCondition
    as CreateLightGoodsVehicleConditionCmd;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\SubmitApplication;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\AutoGrant as AutoGrantCmd;
use Dvsa\Olcs\Api\Domain\QueryHandler\Organisation\Organisation;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Repository\Sla;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculator;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculatorInterface;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Dvsa\Olcs\Transfer\Command\Application\SubmitApplication as Cmd;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\MocksAbstractCommandHandlerServicesTrait;
use Dvsa\OlcsTest\MocksServicesTrait;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Api\Domain\CommandHandler\Application\SubmitApplication
 */
class SubmitApplicationTest extends AbstractCommandHandlerTestCase
{
    use MocksServicesTrait;
    use MocksAbstractCommandHandlerServicesTrait;

    public const APP_ID = 9001;
    public const LIC_ID = 8001;
    public const TASK_ID = 6001;
    public const VERSION = 10;
    public const TRAFFIC_AREA = 'TA';

    /** @var SubmitApplication */
    protected $sut;
    /** @var  Entity\Licence\Licence | m\MockInterface */
    private $mockLic;
    /** @var  Entity\Application\Application  | m\MockInterface */
    private $mockApp;

    private OrganisationEntity $organisation;

    /** @var  m\MockInterface */
    private $mockTmaRepo;

    public function setUp(): void
    {
        $this->sut = new SubmitApplication();

        $this->mockRepo('Application', Repository\Application::class);
        $this->mockTmaRepo = $this->mockRepo(
            'TransportManagerApplication',
            Repository\TransportManagerApplication::class
        );
        $this->mockRepo('Sla', Sla::class);

        $trafficArea = new Entity\TrafficArea\TrafficArea();
        $trafficArea->setId(self::TRAFFIC_AREA);

        $this->organisation = new OrganisationEntity();
        $this->organisation->setType(new RefData(OrganisationEntity::ORG_TYPE_OTHER));

        $this->mockLic = m::mock(LicenceEntity::class)->makePartial();
        $this->mockLic
            ->setTrafficArea($trafficArea)
            ->setLicenceType(new RefData())
            ->setOperatingCentres(new \Doctrine\Common\Collections\ArrayCollection())
            ->setOrganisation($this->organisation);

        $this->mockApp = m::mock(ApplicationEntity::class)->makePartial();
        $this->mockApp
            ->setLicence($this->mockLic)
            ->setOperatingCentres(new \Doctrine\Common\Collections\ArrayCollection());

        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class),
            SlaCalculatorInterface::class => m::mock(SlaCalculator::class),
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        parent::setUp();

        $this->mockApp
            ->setStatus($this->mapRefdata(ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED))
            ->setLicenceType($this->mapRefdata(LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL));
    }

    protected function initReferences()
    {
        $this->refData = [
            ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED,
            ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION,
            LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION,
            LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
            LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED,
            LicenceEntity::LICENCE_CATEGORY_PSV,
            \Dvsa\Olcs\Api\Entity\Application\S4::STATUS_APPROVED,
        ];

        $this->references = [
            ApplicationEntity::class => [
                self::APP_ID => $this->mockApp,
            ],
            LicenceEntity::class => [
                self::LIC_ID => $this->mockLic,
            ],
        ];

        parent::initReferences();
    }

    /**
     * @dataProvider dpTestHandleCommand
     */
    public function testHandleCommand($isVariation, $expected, $isInternalUser, $goodOrPsv, $hasS4)
    {
        $this->setupIsInternalUser($isInternalUser);

        $now           = new DateTime();

        $command = Cmd::create(
            [
                'id' => self::APP_ID,
                'version' => self::VERSION,
            ]
        );

        $mockedSlaEntity = m::mock(\Dvsa\Olcs\Api\Entity\System\Sla::class);

        $this->repoMap['Sla']
            ->expects('fetchByCategoryFieldAndCompareTo')
            ->with('application', 'receivedDate', 'targetCompletionDate')
            ->andReturn($mockedSlaEntity);

        $this->mockApp
            ->setIsVariation($isVariation)
            ->setS4s(new \Doctrine\Common\Collections\ArrayCollection())
            ->setApplicationCompletion(new ApplicationCompletion($this->mockApp))
            ->setGoodsOrPsv(new RefData($goodOrPsv))
            //  mocked methods
            ->shouldReceive('setStatus')
            ->with($this->mapRefdata(ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION))
            ->andReturnSelf()
            ->shouldReceive('getCode')->andReturn('TEST CODE')
            ->shouldReceive('isPublishable')->andReturn(true);

        if ($hasS4) {
            $s4 = new Entity\Application\S4($this->mockApp, $this->mockLic);
            $s4->setOutcome($this->mapRefdata(Entity\Application\S4::STATUS_APPROVED));
            $this->mockApp->setS4s(new \Doctrine\Common\Collections\ArrayCollection([$s4]));
        }

        $expectedTargetCompletionDate = clone $now;
        $expectedTargetCompletionDate->modify('+8 week');

        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->expects('applySla')
            ->with(m::type(\DateTimeInterface::class), $mockedSlaEntity, null)
            ->andReturn($expectedTargetCompletionDate);

        // licence status should be updated if application is not a variation
        if ($isVariation) {
            $this->mockLic
                ->shouldReceive('setStatus')
                ->never();
        } else {
            $this->mockLic
                ->shouldReceive('setStatus')
                ->with($this->mapRefdata(LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION))
                ->once()
                ->andReturnSelf();
        }

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, self::VERSION)
            ->andReturn($this->mockApp);

        $this->repoMap['Application']
            ->shouldReceive('save')
            ->with($this->mockApp)
            ->once();

        $expectedTaskData = [
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'subCategory' => CategoryEntity::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
            'description' => 'TEST CODE Application',
            'actionDate' => $now->format('Y-m-d'),
            'assignedToUser' => null,
            'assignedToTeam' => null,
            'isClosed' => false,
            'urgent' => false,
            'application' => self::APP_ID,
            'licence' => self::LIC_ID,
            'busReg' => null,
            'case' => null,
            'transportManager' => null,
            'irfoOrganisation' => null,
        ];
        $taskResult = new Result();
        $taskResult->addId('task', self::TASK_ID);
        $taskResult->addMessage('task created');
        $this->expectedSideEffect(CreateTaskCmd::class, $expectedTaskData, $taskResult);

        $this->expectedSideEffectAsSystemUser(
            CreateLightGoodsVehicleConditionCmd::class,
            ['applicationId' => self::APP_ID],
            (new Result())->addMessage('unit LightGoodsVehicleCondition created')
        );

        $result1 = new Result();
        $result1->addMessage('Snapshot created');
        $this->expectedSideEffect(
            CreateSnapshot::class,
            ['id' => self::APP_ID, 'event' => CreateSnapshot::ON_SUBMIT],
            $result1
        );

        if (
            !$isInternalUser
            && !(
                $isVariation
                && $goodOrPsv === LicenceEntity::LICENCE_CATEGORY_PSV
            )
            && !$hasS4
        ) {
            $this->expectedSideEffect(
                \Dvsa\Olcs\Transfer\Command\Publication\Application::class,
                [
                    'id' => self::APP_ID,
                    'trafficArea' => self::TRAFFIC_AREA,
                ],
                (new Result())->addMessage('unit Publication created')
            );

            $this->expectedSideEffect(
                \Dvsa\Olcs\Api\Domain\Command\Application\CreateTexTask::class,
                ['id' => self::APP_ID],
                (new Result())->addMessage('unit TexTask created')
            );
        }

        $this->expectedLicenceCacheClear($this->mockLic);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($expectedTargetCompletionDate, $this->mockApp->getTargetCompletionDate());
        $this->assertEquals($now, $this->mockApp->getReceivedDate());

        $this->assertEquals($expected, $result->toArray());
    }

    public function dpTestHandleCommand()
    {
        return [
            'new app' => [
                'isVariation' => false,
                'expected' => [
                    'id' => [
                        'application' => self::APP_ID,
                        'licence' => self::LIC_ID,
                        'task' => self::TASK_ID,
                    ],
                    'messages' => [
                        'Snapshot created',
                        'Application updated',
                        'Licence updated',
                        'task created',
                        'unit LightGoodsVehicleCondition created',
                        'unit Publication created',
                        'unit TexTask created',
                    ],
                ],
                'isInternalUser' => false,
                'goodsOrPsv' => LicenceEntity::LICENCE_CATEGORY_PSV,
                'hasS4' => false,
            ],
            'new app S4' => [
                'isVariation' => false,
                'expected' => [
                    'id' => [
                        'application' => self::APP_ID,
                        'licence' => self::LIC_ID,
                        'task' => self::TASK_ID,
                    ],
                    'messages' => [
                        'Snapshot created',
                        'Application updated',
                        'Licence updated',
                        'task created',
                        'unit LightGoodsVehicleCondition created',
                    ],
                ],
                'isInternalUser' => false,
                'goodsOrPsv' => LicenceEntity::LICENCE_CATEGORY_PSV,
                'hasS4' => true,
            ],
            'variation' => [
                'isVariation' => true,
                'expected' => [
                    'id' => [
                        'application' => self::APP_ID,
                        'task' => self::TASK_ID,
                    ],
                    'messages' => [
                        'Snapshot created',
                        'Application updated',
                        'task created',
                        'unit LightGoodsVehicleCondition created',
                        'unit Publication created',
                        'unit TexTask created',
                    ],
                ],
                'isInternalUser' => false,
                'goodsOrPsv' => LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
                'hasS4' => false,
            ],
            'variation S4' => [
                'isVariation' => true,
                'expected' => [
                    'id' => [
                        'application' => self::APP_ID,
                        'task' => self::TASK_ID,
                    ],
                    'messages' => [
                        'Snapshot created',
                        'Application updated',
                        'task created',
                        'unit LightGoodsVehicleCondition created',
                    ],
                ],
                'isInternalUser' => false,
                'goodsOrPsv' => LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
                'hasS4' => true,
            ],
            'new app internal' => [
                false,
                [
                    'id' => [
                        'application' => self::APP_ID,
                        'licence' => self::LIC_ID,
                        'task' => self::TASK_ID,
                    ],
                    'messages' => [
                        'Snapshot created',
                        'Application updated',
                        'Licence updated',
                        'task created',
                        'unit LightGoodsVehicleCondition created',
                    ],
                ],
                true,
                'goodsOrPsv' => LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
                'hasS4' => true,
            ]
        ];
    }

    public function testHandleCommandVariationPsv()
    {
        $this->setupIsInternalUser(false);

        $now           = new DateTime();

        $command = Cmd::create(
            [
                'id' => self::APP_ID,
                'version' => self::VERSION,
            ]
        );

        $mockedSlaEntity = m::mock(\Dvsa\Olcs\Api\Entity\System\Sla::class);

        $this->repoMap['Sla']
            ->expects('fetchByCategoryFieldAndCompareTo')
            ->with('application', 'receivedDate', 'targetCompletionDate')
            ->andReturn($mockedSlaEntity);

        /* @var $application ApplicationEntity | m\MockInterface */
        $application = $this->mapReference(ApplicationEntity::class, self::APP_ID);
        $application->setIsVariation(true);
        $application->setS4s(new \Doctrine\Common\Collections\ArrayCollection());
        $application->setGoodsOrPsv(new RefData(LicenceEntity::LICENCE_CATEGORY_PSV));
        $application->setApplicationCompletion(new ApplicationCompletion($application));
        $application
            ->shouldReceive('setStatus')
            ->with($this->mapRefdata(ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION))
            ->andReturnSelf()
            ->shouldReceive('getCode')
            ->andReturn('TEST CODE');

        $expectedTargetCompletionDate = clone $now;
        $expectedTargetCompletionDate->modify('+8 week');

        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->expects('applySla')
            ->with(m::type(\DateTimeInterface::class), $mockedSlaEntity, null)
            ->andReturn($expectedTargetCompletionDate);

        // licence status should be updated if application is not a variation
        $this->mockLic
            ->shouldReceive('setStatus')
            ->never();

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, self::VERSION)
            ->andReturn($application);

        $this->repoMap['Application']
            ->shouldReceive('save')
            ->with($application)
            ->once();

        $expectedTaskData = [
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'subCategory' => CategoryEntity::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
            'description' => 'TEST CODE Application',
            'actionDate' => $now->format('Y-m-d'),
            'assignedToUser' => null,
            'assignedToTeam' => null,
            'isClosed' => false,
            'urgent' => false,
            'application' => self::APP_ID,
            'licence' => self::LIC_ID,
            'busReg' => null,
            'case' => null,
            'transportManager' => null,
            'irfoOrganisation' => null,
        ];
        $taskResult = new Result();
        $taskResult->addId('task', self::TASK_ID);
        $taskResult->addMessage('task created');
        $this->expectedSideEffect(CreateTaskCmd::class, $expectedTaskData, $taskResult);

        $this->expectedSideEffectAsSystemUser(
            CreateLightGoodsVehicleConditionCmd::class,
            ['applicationId' => self::APP_ID],
            (new Result())->addMessage('unit LightGoodsVehicleCondition created')
        );

        $result1 = new Result();
        $result1->addMessage('Snapshot created');
        $this->expectedSideEffect(
            CreateSnapshot::class,
            ['id' => self::APP_ID, 'event' => CreateSnapshot::ON_SUBMIT],
            $result1
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($expectedTargetCompletionDate, $application->getTargetCompletionDate());
        $this->assertEquals($now, $application->getReceivedDate());
        $expected = [
            'id' => [
                'application' => self::APP_ID,
                'task' => self::TASK_ID,
            ],
            'messages' => [
                'Snapshot created',
                'Application updated',
                'task created',
                'unit LightGoodsVehicleCondition created',
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandSpecialRestricted()
    {
        $this->setupIsInternalUser(false);

        $now           = new DateTime();

        $expected = [
            'id' => [
                'application' => self::APP_ID,
                'licence' => self::LIC_ID,
                'task' => self::TASK_ID,
            ],
            'messages' => [
                'Snapshot created',
                'Application updated',
                'Licence updated',
                'task created',
                'unit LightGoodsVehicleCondition created',
            ],
        ];

        $command = Cmd::create(
            [
                'id' => self::APP_ID,
                'version' => self::VERSION,
            ]
        );

        $mockedSlaEntity = m::mock(\Dvsa\Olcs\Api\Entity\System\Sla::class);

        $this->repoMap['Sla']
            ->expects('fetchByCategoryFieldAndCompareTo')
            ->with('application', 'receivedDate', 'targetCompletionDate')
            ->andReturn($mockedSlaEntity);

        /** @var ApplicationEntity | m\MockInterface $application */
        $application = $this->mapReference(ApplicationEntity::class, self::APP_ID);
        $application->setIsVariation(false);
        $application->setLicenceType($this->mapRefdata(LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED));
        $application->setS4s(new \Doctrine\Common\Collections\ArrayCollection());
        $application->setGoodsOrPsv($this->mapRefdata(LicenceEntity::LICENCE_CATEGORY_PSV));

        $application
            ->shouldReceive('setStatus')
            ->with($this->mapRefdata(ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION))
            ->andReturnSelf()
            ->shouldReceive('getCode')
            ->andReturn('TEST CODE');

        $expectedTargetCompletionDate = clone $now;
        $expectedTargetCompletionDate->modify('+8 week');

        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->expects('applySla')
            ->with(m::type(\DateTimeInterface::class), $mockedSlaEntity, null)
            ->andReturn($expectedTargetCompletionDate);

        // licence status should be updated if application is not a variation
        $this->mockLic
            ->shouldReceive('setStatus')
            ->with($this->mapRefdata(LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION))
            ->once()
            ->andReturnSelf();

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, self::VERSION)
            ->andReturn($application);

        $this->repoMap['Application']
            ->shouldReceive('save')
            ->with($application)
            ->once();

        $expectedTaskData = [
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'subCategory' => CategoryEntity::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
            'description' => 'TEST CODE Application',
            'actionDate' => $now->format('Y-m-d'),
            'assignedToUser' => null,
            'assignedToTeam' => null,
            'isClosed' => false,
            'urgent' => false,
            'application' => self::APP_ID,
            'licence' => self::LIC_ID,
            'busReg' => null,
            'case' => null,
            'transportManager' => null,
            'irfoOrganisation' => null,
        ];
        $taskResult = new Result();
        $taskResult->addId('task', self::TASK_ID);
        $taskResult->addMessage('task created');
        $this->expectedSideEffect(CreateTaskCmd::class, $expectedTaskData, $taskResult);

        $this->expectedSideEffectAsSystemUser(
            CreateLightGoodsVehicleConditionCmd::class,
            ['applicationId' => self::APP_ID],
            (new Result())->addMessage('unit LightGoodsVehicleCondition created')
        );

        $result1 = new Result();
        $result1->addMessage('Snapshot created');
        $this->expectedSideEffect(
            CreateSnapshot::class,
            ['id' => self::APP_ID, 'event' => CreateSnapshot::ON_SUBMIT],
            $result1
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($expectedTargetCompletionDate, $application->getTargetCompletionDate());
        $this->assertEquals($now, $application->getReceivedDate());

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * @dataProvider dataProviderApplicationCompletion
     */
    public function testVariationTasksCreated(
        $applicationCompletion,
        $isLtd,
        $tmaStat,
        $expectedDescription,
        $expectedSubCategory,
        $expectedTaskData,
        $code
    ) {
        $this->setupIsInternalUser(false);

        $now = new DateTime();

        $expectedTaskData['subCategory'] = $expectedSubCategory;
        $expectedTaskData['description'] = $expectedDescription;
        $expectedTaskData['actionDate'] = $now->format('Y-m-d');

        $command = Cmd::create(
            [
                'id' =>  self::APP_ID,
                'version' => self::VERSION,
            ]
        );

        if ($isLtd) {
            $this->organisation->setType(new RefData(OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY));
        }

        $this->mockLic->setOrganisation($this->organisation);

        $mockedSlaEntity = m::mock(\Dvsa\Olcs\Api\Entity\System\Sla::class);

        $this->repoMap['Sla']
            ->expects('fetchByCategoryFieldAndCompareTo')
            ->with('application', 'receivedDate', 'targetCompletionDate')
            ->andReturn($mockedSlaEntity);

        /** @var ApplicationEntity | m\MockInterface $application */
        $application = $this->mapReference(ApplicationEntity::class, self::APP_ID);
        $application->setIsVariation(true);
        $application->shouldReceive('getVariationCompletion')->with()->andReturn($applicationCompletion);
        $application
            ->shouldReceive('setStatus')
            ->with($this->mapRefdata(ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION))
            ->andReturnSelf()
            ->shouldReceive('getCode')
            ->andReturn($code);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($application, $this->mockLic);
        $s4->setOutcome($this->mapRefdata(\Dvsa\Olcs\Api\Entity\Application\S4::STATUS_APPROVED));
        $application->setS4s(new \Doctrine\Common\Collections\ArrayCollection([$s4]));

        $expectedTargetCompletionDate = clone $now;
        $expectedTargetCompletionDate->modify('+8 week');

        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->expects('applySla')
            ->with(m::type(\DateTimeInterface::class), $mockedSlaEntity, null)
            ->andReturn($expectedTargetCompletionDate);

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, self::VERSION)
            ->andReturn($application);

        $this->repoMap['Application']
            ->shouldReceive('save')
            ->with($application)
            ->once();

        if (!empty($tmaStat)) {
            $this->mockTmaRepo->shouldReceive('fetchStatByAppId')->once()->with(self::APP_ID)->andReturn($tmaStat);
        }

        $taskResult = new Result();
        $taskResult->addId('task', self::TASK_ID);
        $taskResult->addMessage('task created');
        $this->expectedSideEffect(CreateTaskCmd::class, $expectedTaskData, $taskResult);

        $this->expectedSideEffectAsSystemUser(
            CreateLightGoodsVehicleConditionCmd::class,
            ['applicationId' => self::APP_ID],
            (new Result())->addMessage('unit LightGoodsVehicleCondition created')
        );

        $result1 = new Result();
        $result1->addMessage('Snapshot created');
        $this->expectedSideEffect(
            CreateSnapshot::class,
            ['id' => self::APP_ID, 'event' => CreateSnapshot::ON_SUBMIT],
            $result1
        );

        $this->sut->handleCommand($command);

        $this->assertEquals($expectedTargetCompletionDate, $application->getTargetCompletionDate());
        $this->assertEquals($now, $application->getReceivedDate());
    }

    public function dataProviderApplicationCompletion()
    {
        $expectedTaskData = [
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'assignedToUser' => null,
            'assignedToTeam' => null,
            'isClosed' => false,
            'urgent' => false,
            'application' =>  self::APP_ID,
            'licence' => self::LIC_ID,
            'busReg' => null,
            'case' => null,
            'transportManager' => null,
            'irfoOrganisation' => null,
        ];

        return [
            'peopleOnlyLtd' => [
                [
                    ApplicationCompletion::SECTION_PEOPLE => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_TRANSPORT_MANAGER => ApplicationCompletion::STATUS_NOT_STARTED,
                    ApplicationCompletion::SECTION_FINANCIAL_HISTORY => ApplicationCompletion::STATUS_COMPLETE,
                ],
                true,
                [],
                'Director change application',
                \Dvsa\Olcs\Api\Entity\System\Category::TASK_SUB_CATEGORY_DIRECTOR_CHANGE_DIGITAL,
                $expectedTaskData,
                'TEST CODE'
            ],
            'peopleOnlyLtdFail' => [
                [
                    ApplicationCompletion::SECTION_TRANSPORT_MANAGER => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_PEOPLE => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_FINANCIAL_HISTORY => ApplicationCompletion::STATUS_COMPLETE,
                ],
                true,
                [],
                'TEST CODE Application',
                \Dvsa\Olcs\Api\Entity\System\Category::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
                $expectedTaskData,
                'TEST CODE'
            ],
            'peopleOnlyOther' => [
                [
                    ApplicationCompletion::SECTION_PEOPLE => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_TRANSPORT_MANAGER => ApplicationCompletion::STATUS_NOT_STARTED,
                    ApplicationCompletion::SECTION_FINANCIAL_HISTORY => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_DECLARATION_INTERNAL => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_DECLARATION => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_CONVICTIONS_AND_PENALTIES => ApplicationCompletion::STATUS_COMPLETE,
                ],
                false,
                [],
                'Partner change application',
                \Dvsa\Olcs\Api\Entity\System\Category::TASK_SUB_CATEGORY_PARTNER_CHANGE_DIGITAL,
                $expectedTaskData,
                'TEST CODE'
            ],
            'peopleOnlyOtherFail' => [
                [
                    ApplicationCompletion::SECTION_PEOPLE => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_TRANSPORT_MANAGER => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_FINANCIAL_HISTORY => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_DECLARATION_INTERNAL => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_DECLARATION => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_CONVICTIONS_AND_PENALTIES => ApplicationCompletion::STATUS_COMPLETE,
                ],
                true,
                [],
                'TEST CODE Application',
                \Dvsa\Olcs\Api\Entity\System\Category::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
                $expectedTaskData,
                'TEST CODE'
            ],
            'tmOnly' => [
                [
                    ApplicationCompletion::SECTION_TRANSPORT_MANAGER => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_FINANCIAL_HISTORY => ApplicationCompletion::STATUS_COMPLETE,
                ],
                true,
                'tmpStat' => [
                    'action' => [
                        Entity\Tm\TransportManagerApplication::ACTION_ADD => 1,
                        Entity\Tm\TransportManagerApplication::ACTION_DELETE => 0,
                    ]
                ],
                'TM change variation',
                \Dvsa\Olcs\Api\Entity\System\Category::TASK_SUB_CATEGORY_APPLICATION_TM1_DIGITAL,
                $expectedTaskData,
                'TEST CODE'
            ],
            'tmOnlyDeleteOnly' => [
                [
                    ApplicationCompletion::SECTION_TRANSPORT_MANAGER => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_FINANCIAL_HISTORY => ApplicationCompletion::STATUS_COMPLETE,
                ],
                true,
                'tmpStat' => [
                    'action' => [
                        Entity\Tm\TransportManagerApplication::ACTION_ADD => '0',
                        Entity\Tm\TransportManagerApplication::ACTION_DELETE => '1',
                    ]
                ],
                'TM1 (Removal only)',
                \Dvsa\Olcs\Api\Entity\System\Category::TASK_SUB_CATEGORY_APPLICATION_TM1_REMOVAL_VARIATION,
                $expectedTaskData,
                'TEST CODE'
            ],
            'tmOnlyFail' => [
                [
                    ApplicationCompletion::SECTION_PEOPLE => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_TRANSPORT_MANAGER => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_FINANCIAL_HISTORY => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_DECLARATION_INTERNAL => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_DECLARATION => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_CONVICTIONS_AND_PENALTIES => ApplicationCompletion::STATUS_COMPLETE,
                ],
                true,
                'tmpStat' => [],
                'TEST CODE Application',
                \Dvsa\Olcs\Api\Entity\System\Category::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
                $expectedTaskData,
                'TEST CODE'
            ],
            'GV80A' => [
                [
                    ApplicationCompletion::SECTION_PEOPLE => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_TRANSPORT_MANAGER => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_FINANCIAL_HISTORY => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_DECLARATION_INTERNAL => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_DECLARATION => ApplicationCompletion::STATUS_COMPLETE,
                    ApplicationCompletion::SECTION_CONVICTIONS_AND_PENALTIES => ApplicationCompletion::STATUS_COMPLETE,
                ],
                true,
                [],
                'GV80A Application',
                \Dvsa\Olcs\Api\Entity\System\Category::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
                array_merge($expectedTaskData, ['urgent' => 'Y']),
                'GV80A'
            ]
        ];
    }

    public function testHandleInvalidStatus()
    {
        $command = Cmd::create(
            [
                'id' => self::APP_ID,
                'version' => self::VERSION,
            ]
        );

        /** @var ApplicationEntity $application */
        $application = $this->mapReference(ApplicationEntity::class, self::APP_ID);
        $application->setStatus($this->mapRefdata(ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION));

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, self::VERSION)
            ->andReturn($application);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithAutoGrant()
    {
        $this->setupIsInternalUser(false);

        $now = new \DateTime();
        $command = Cmd::create([
            'id' => self::APP_ID,
            'version' => self::VERSION,
        ]);

        $mockedSlaEntity = m::mock(\Dvsa\Olcs\Api\Entity\System\Sla::class);

        $this->repoMap['Sla']
            ->expects('fetchByCategoryFieldAndCompareTo')
            ->with('application', 'receivedDate', 'targetCompletionDate')
            ->andReturn($mockedSlaEntity);

        $this->mockApp
            ->setIsVariation(true)
            ->setS4s(new \Doctrine\Common\Collections\ArrayCollection())
            ->setApplicationCompletion(new \Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion($this->mockApp))
            ->setGoodsOrPsv($this->mapRefdata(\Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_CATEGORY_GOODS_VEHICLE))
            ->setConditionUndertakings(new \Doctrine\Common\Collections\ArrayCollection())
            ->setApplicationOrganisationPersons(new \Doctrine\Common\Collections\ArrayCollection())
            ->setLicenceVehicles(new \Doctrine\Common\Collections\ArrayCollection())
            ->setTransportManagers(new \Doctrine\Common\Collections\ArrayCollection())
            ->setStatus($this->mapRefdata(ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED));

        $this->mockApp
            ->shouldReceive('canAutoGrant')
            ->andReturn(true)
            ->shouldReceive('getCode')
            ->andReturn('GV79')
            ->shouldReceive('setStatus')
            ->with($this->mapRefdata(ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION))
            ->andReturnSelf();


        $expectedTargetCompletionDate = clone $now;
        $expectedTargetCompletionDate->modify('+8 week');

        $this->mockedSmServices[\Dvsa\Olcs\Api\Domain\Util\SlaCalculatorInterface::class]
            ->expects('applySla')
            ->with(m::type(\DateTimeInterface::class), $mockedSlaEntity, null)
            ->andReturn($expectedTargetCompletionDate);

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, self::VERSION)
            ->andReturn($this->mockApp);

        $this->repoMap['Application']
            ->shouldReceive('save')
            ->with($this->mockApp)
            ->once();

        // Expect snapshot to be created
        $snapshotResult = new Result();
        $snapshotResult->addMessage('Snapshot created');
        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot::class,
            ['id' => self::APP_ID, 'event' => \Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot::ON_SUBMIT],
            $snapshotResult
        );

        // Expect auto-grant command to be called
        $autoGrantResult = new Result();
        $autoGrantResult->addMessage('Application auto-granted');
        $autoGrantResult->setFlag('autoGranted', true);
        $this->expectedSideEffectAsSystemUser(
            AutoGrantCmd::class,
            ['id' => self::APP_ID],
            $autoGrantResult
        );

        // Expect task creation
        // Expect task creation - must match exact structure
        $expectedTaskData = [
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'subCategory' => CategoryEntity::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
            'description' => 'GV79 Application',
            'actionDate' => $now->format('Y-m-d'),
            'assignedToUser' => null,
            'assignedToTeam' => null,
            'isClosed' => false,
            'urgent' => false,
            'application' => self::APP_ID,
            'licence' => self::LIC_ID,
            'busReg' => null,
            'case' => null,
            'transportManager' => null,
            'irfoOrganisation' => null,
        ];
        $taskResult = new Result();
        $taskResult->addId('task', self::TASK_ID);
        $taskResult->addMessage('task created');
        $this->expectedSideEffect(CreateTaskCmd::class, $expectedTaskData, $taskResult);

        // Expect light goods vehicle condition check
        $lgvResult = new Result();
        $this->expectedSideEffectAsSystemUser(
            \Dvsa\Olcs\Api\Domain\Command\ConditionUndertaking\CreateLightGoodsVehicleCondition::class,
            ['applicationId' => self::APP_ID],
            $lgvResult
        );

        $this->expectedLicenceCacheClear($this->mockLic);

        $result = $this->sut->handleCommand($command);

        // Verify the auto-grant message is in the results
        $messages = $result->getMessages();
        $this->assertContains('Application auto-granted', $messages);

        // Verify the auto-granted flag is set
        $this->assertTrue($result->getFlag('autoGranted'));
    }
}
