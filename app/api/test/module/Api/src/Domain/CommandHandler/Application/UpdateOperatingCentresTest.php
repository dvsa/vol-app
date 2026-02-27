<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\HandleOcVariationFees;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Service\UpdateOperatingCentreHelper;
use Dvsa\Olcs\Api\Domain\Service\VariationOperatingCentreHelper;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Application\UpdateOperatingCentres as Cmd;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateTrafficArea;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateOperatingCentres as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\OlcsTest\MocksServicesTrait;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\MocksAbstractCommandHandlerServicesTrait;
use LmcRbacMvc\Service\AuthorizationService;
use Hamcrest\Arrays\IsArrayContainingKeyValuePair;
use Hamcrest\Core\AllOf;
use Dvsa\OlcsTest\Api\Entity\Licence\LicenceBuilder;
use Dvsa\OlcsTest\Api\Entity\Application\ApplicationBuilder;
use Dvsa\OlcsTest\Api\Domain\Repository\MocksApplicationRepositoryTrait;
use Dvsa\OlcsTest\Api\Domain\Repository\MocksApplicationOperatingCentreRepositoryTrait;
use Dvsa\OlcsTest\Api\Domain\Repository\MocksLicenceOperatingCentreRepositoryTrait;

/**
 * @see \Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateOperatingCentres
 */
class UpdateOperatingCentresTest extends AbstractCommandHandlerTestCase
{
    use MocksServicesTrait;
    use MocksAbstractCommandHandlerServicesTrait;
    use ProvidesOperatingCentreVehicleAuthorizationConstraintsTrait;
    use MocksApplicationRepositoryTrait;
    use MocksApplicationOperatingCentreRepositoryTrait;
    use MocksLicenceOperatingCentreRepositoryTrait;

    protected const TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY = 'totAuthHgvVehicles';
    protected const ID_COMMAND_PROPERTY = 'id';
    protected const TOT_AUTH_LGV_VEHICLES_COMMAND_PROPERTY = 'totAuthLgvVehicles';
    protected const TOT_COMMUNITY_LICENCES_COMMAND_PROPERTY = 'totCommunityLicences';
    protected const COMMUNITY_LICENCE_COUNT_TOO_HIGH_VALIDATION_ERROR_CODE = 'ERR_OC_CL_1';
    protected const VALIDATION_MESSAGES = ['A VALIDATION MESSAGE KEY' => 'A VALIDATION MESSAGE VALUE'];
    protected const ONE_HGV = 1;
    protected const ONE_LGV = 1;
    protected const NO_HGVS = 1;
    protected const TWO_LGVS = 2;

    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->handleCommand(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    public function testHandleCommandPartialMissingTa(): void
    {
        $this->setUpLegacy();
        $data = [
            'id' => 111,
            'version' => 1,
            'partial' => true,
            'partialAction' => 'add',
            'trafficArea' => null

        ];
        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();

        $aocs = new ArrayCollection();
        $aocs->add($aoc);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setIsVariation(false);
        $application->setLicence($licence);
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $this->mockedSmServices['UpdateOperatingCentreHelper']->shouldReceive('addMessage')
            ->once()
            ->with('trafficArea', 'ERR_OC_TA_1')
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn(['foo' => 'bar']);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    public function testHandleCommandPsvTooManyCommLic(): void
    {
        $this->setUpLegacy();
        $data = [
            'id' => 111,
            'version' => 1,
            'partial' => false,
            'trafficArea' => null,
            'totCommunityLicences' => 10,
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => 8

        ];
        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        $aocs = new ArrayCollection();
        $aocs->add('foo');

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(111);
        $application->setIsVariation(false);
        $application->setLicence($licence);
        $application->shouldReceive('isPsv')->andReturn(true);
        $application->shouldReceive('canHaveCommunityLicences')->andReturn(true);
        $application->shouldReceive('getTrafficArea->getId')->andReturn('TA');
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $this->mockedSmServices['UpdateOperatingCentreHelper']
            ->shouldReceive('validatePsv')
            ->once()
            ->with($application, $command)
            ->shouldReceive('addMessage')
            ->once()
            ->with('totCommunityLicences', 'ERR_OC_CL_1')
            ->shouldReceive('validateEnforcementArea')
            ->once()
            ->with($application, $command)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn(['foo' => 'bar']);

        $this->mockedSmServices['TrafficAreaValidator']->shouldReceive('validateForSameTrafficAreas')->andReturn(false);

        $aoc = [
            'noOfVehiclesRequired' => 10,
            'noOfTrailersRequired' => 10,
        ];

        $aocs = [
            $aoc
        ];

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('fetchByApplicationIdForOperatingCentres')
            ->with(111)
            ->andReturn($aocs);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    public function testHandleCommandGvInvalid(): void
    {
        $this->setUpLegacy();
        $data = [
            'id' => 111,
            'version' => 1,
            'partial' => false,
            'trafficArea' => null,
            'totCommunityLicences' => 8,
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => 8

        ];
        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        $aocs = new ArrayCollection();
        $aocs->add('foo');

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(111);
        $application->setIsVariation(true);
        $application->setLicence($licence);
        $application->shouldReceive('isPsv')->andReturn(false);
        $application->shouldReceive('canHaveCommunityLicences')->andReturn(true);
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $expectedTotalsMatcher = AllOf::allOf(
            IsArrayContainingKeyValuePair::hasKeyValuePair('noOfOperatingCentres', 1),
            IsArrayContainingKeyValuePair::hasKeyValuePair('minTrailerAuth', 10),
            IsArrayContainingKeyValuePair::hasKeyValuePair('maxTrailerAuth', 10)
        );

        $this->mockedSmServices['UpdateOperatingCentreHelper']->shouldReceive('validateTotalAuthTrailers')
            ->once()
            ->with($application, $command, $expectedTotalsMatcher)
            ->shouldReceive('validateTotalAuthHgvVehicles')
            ->once()
            ->with($application, $command, $expectedTotalsMatcher)
            ->shouldReceive('validateEnforcementArea')
            ->once()
            ->with($application, $command)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn(['foo' => 'bar']);

        $aocs = [
            [
                'action' => 'A',
                'noOfVehiclesRequired' => 10,
                'noOfTrailersRequired' => 10,
            ],
            [
                'action' => 'D'
            ]
        ];

        $this->mockedSmServices['VariationOperatingCentreHelper']->shouldReceive('getListDataForApplication')
            ->with($application)
            ->andReturn($aocs);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    public function testHandleCommandGvValid(): void
    {
        $this->setUpLegacy();
        $data = [
            'id' => 111,
            'version' => 1,
            'partial' => false,
            'trafficArea' => 'A',
            'enforcementArea' => 'A111',
            'totCommunityLicences' => 10,
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => 10,
            'totAuthTrailers' => 10,
        ];
        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->setVersion(1);

        $aocs = new ArrayCollection();
        $aocs->add('foo');

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(111);
        $application->setIsVariation(false);
        $application->setLicence($licence);
        $application->shouldReceive('isPsv')->andReturn(false);
        $application->shouldReceive('canHaveCommunityLicences')->andReturn(true);
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $expectedTotalsMatcher = AllOf::allOf(
            IsArrayContainingKeyValuePair::hasKeyValuePair('noOfOperatingCentres', 1),
            IsArrayContainingKeyValuePair::hasKeyValuePair('minTrailerAuth', 10),
            IsArrayContainingKeyValuePair::hasKeyValuePair('maxTrailerAuth', 10)
        );

        $this->mockedSmServices['UpdateOperatingCentreHelper']->shouldReceive('validateTotalAuthTrailers')
            ->once()
            ->with($application, $command, $expectedTotalsMatcher)
            ->shouldReceive('validateTotalAuthHgvVehicles')
            ->once()
            ->with($application, $command, $expectedTotalsMatcher)
            ->shouldReceive('validateEnforcementArea')
            ->once()
            ->with($application, $command)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn([]);

        $this->mockedSmServices['TrafficAreaValidator']->shouldReceive('validateForSameTrafficAreas')->andReturn(false);

        $aocs = [
            [
                'action' => 'A',
                'noOfVehiclesRequired' => 10,
                'noOfTrailersRequired' => 10,
            ]
        ];

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('fetchByApplicationIdForOperatingCentres')
            ->with(111)
            ->andReturn($aocs);

        $data = [
            'id' => 222,
            'version' => 1,
            'trafficArea' => 'A'
        ];
        $result = new Result();
        $result->addMessage('UpdateTrafficArea');
        $this->expectedSideEffect(UpdateTrafficArea::class, $data, $result);

        $this->repoMap['Application']->shouldReceive('save')
            ->with($application);

        $data = [
            'id' => 111,
            'section' => 'operatingCentres'
        ];
        $result = new Result();
        $result->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'UpdateTrafficArea',
                'Application record updated',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    public function testHandleCommandTrafficAreaValidation(): void
    {
        $this->setUpLegacy();
        $data = [
            'id' => 111,
            'version' => 1,
            'partial' => false,
            'trafficArea' => 'A',
            'enforcementArea' => 'A111',
            'totCommunityLicences' => 10,
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => 10,
            'totAuthTrailers' => 10,
        ];
        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->setVersion(1);

        $aocs = new ArrayCollection();
        $aocs->add('foo');

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(111);
        $application->setIsVariation(false);
        $application->setLicence($licence);
        $application->shouldReceive('isPsv')->andReturn(false);
        $application->shouldReceive('canHaveCommunityLicences')->andReturn(true);
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $expectedTotalsMatcher = AllOf::allOf(
            IsArrayContainingKeyValuePair::hasKeyValuePair('noOfOperatingCentres', 1),
            IsArrayContainingKeyValuePair::hasKeyValuePair('minTrailerAuth', 10),
            IsArrayContainingKeyValuePair::hasKeyValuePair('maxTrailerAuth', 10)
        );

        $this->mockedSmServices['UpdateOperatingCentreHelper']->shouldReceive('validateTotalAuthTrailers')
            ->once()
            ->with($application, $command, $expectedTotalsMatcher)
            ->shouldReceive('validateTotalAuthHgvVehicles')
            ->once()
            ->with($application, $command, $expectedTotalsMatcher)
            ->shouldReceive('validateEnforcementArea')
            ->once()
            ->with($application, $command)
            ->shouldReceive('addMessage')
            ->with('trafficArea', 'ERROR_1', 'TA_NAME')
            ->once()
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn([]);

        $this->mockedSmServices['TrafficAreaValidator']->shouldReceive('validateForSameTrafficAreas')->andReturn(
            ['ERROR_1' => 'TA_NAME']
        );

        $aocs = [
            [
                'action' => 'A',
                'noOfVehiclesRequired' => 10,
                'noOfTrailersRequired' => 10,
            ]
        ];

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('fetchByApplicationIdForOperatingCentres')
            ->with(111)
            ->andReturn($aocs);

        $data = [
            'id' => 222,
            'version' => 1,
            'trafficArea' => 'A'
        ];
        $result = new Result();
        $result->addMessage('UpdateTrafficArea');
        $this->expectedSideEffect(UpdateTrafficArea::class, $data, $result);

        $this->repoMap['Application']->shouldReceive('save')
            ->with($application);

        $data = [
            'id' => 111,
            'section' => 'operatingCentres'
        ];
        $result = new Result();
        $result->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'UpdateTrafficArea',
                'Application record updated',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    public function testHandleCommandPsvValid(): void
    {
        $this->setUpLegacy();
        $data = [
            'id' => 111,
            'version' => 1,
            'partial' => false,
            'trafficArea' => 'A',
            'enforcementArea' => 'A111',
            'totCommunityLicences' => 10,
            'totAuthSmallVehicles' => 4,
            'totAuthMediumVehicles' => 3,
            'totAuthLargeVehicles' => 3,
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => 10,
            'totAuthTrailers' => 10,
        ];
        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->setVersion(1);

        $aocs = new ArrayCollection();
        $aocs->add('foo');

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(111);
        $application->setIsVariation(true);
        $application->setLicence($licence);
        $application->shouldReceive('isPsv')->andReturn(true);
        $application->shouldReceive('canHaveCommunityLicences')->andReturn(true);
        $application->shouldReceive('canHaveLargeVehicles')->andReturn(true);
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $expectedTotalsMatcher = AllOf::allOf(
            IsArrayContainingKeyValuePair::hasKeyValuePair('noOfOperatingCentres', 1),
            IsArrayContainingKeyValuePair::hasKeyValuePair('minTrailerAuth', 10),
            IsArrayContainingKeyValuePair::hasKeyValuePair('maxTrailerAuth', 10)
        );

        $this->mockedSmServices['UpdateOperatingCentreHelper']->shouldReceive('validatePsv')
            ->once()
            ->with($application, $command)
            ->shouldReceive('validateTotalAuthHgvVehicles')
            ->once()
            ->with($application, $command, $expectedTotalsMatcher)
            ->shouldReceive('validateEnforcementArea')
            ->once()
            ->with($application, $command)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn([]);

        $aocs = [
            [
                'action' => 'A',
                'noOfVehiclesRequired' => 10,
                'noOfTrailersRequired' => 10,
            ]
        ];

        $this->mockedSmServices['VariationOperatingCentreHelper']->shouldReceive('getListDataForApplication')
            ->with($application)
            ->andReturn($aocs);

        $this->repoMap['Application']->shouldReceive('save')
            ->with($application);

        $data = [
            'id' => 111,
            'section' => 'operatingCentres'
        ];
        $result = new Result();
        $result->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result);

        $this->expectedSideEffect(
            HandleOcVariationFees::class,
            $data = [
                'id' => 111,
            ],
            (new Result())->addMessage('HandleOcVariationFees')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Application record updated',
                'UpdateApplicationCompletion',
                'HandleOcVariationFees',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    public function testHandleCommandPsvValidVariationWithTa(): void
    {
        $this->setUpLegacy();
        $data = [
            'id' => 111,
            'version' => 1,
            'partial' => false,
            'trafficArea' => 'A',
            'enforcementArea' => 'A111',
            'totCommunityLicences' => 10,
            'totAuthSmallVehicles' => 4,
            'totAuthMediumVehicles' => 3,
            'totAuthLargeVehicles' => 3,
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => 10,
            'totAuthTrailers' => 10,
        ];
        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->setVersion(1);

        $aocs = new ArrayCollection();
        $aocs->add('foo');

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setId(111);
        $application->setIsVariation(true);
        $application->setLicence($licence);
        $application->shouldReceive('isPsv')->andReturn(true);
        $application->shouldReceive('canHaveCommunityLicences')->andReturn(true);
        $application->shouldReceive('canHaveLargeVehicles')->andReturn(true);
        $application->shouldReceive('getTrafficArea')->andReturn('anything');
        $application->setOperatingCentres($aocs);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $expectedTotalsMatcher = AllOf::allOf(
            IsArrayContainingKeyValuePair::hasKeyValuePair('noOfOperatingCentres', 1),
            IsArrayContainingKeyValuePair::hasKeyValuePair('minTrailerAuth', 10),
            IsArrayContainingKeyValuePair::hasKeyValuePair('maxTrailerAuth', 10)
        );

        $this->mockedSmServices['UpdateOperatingCentreHelper']->shouldReceive('validatePsv')
            ->once()
            ->with($application, $command)
            ->shouldReceive('validateTotalAuthHgvVehicles')
            ->once()
            ->with($application, $command, $expectedTotalsMatcher)
            ->shouldReceive('validateEnforcementArea')
            ->once()
            ->with($application, $command)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn([]);

        $aocs = [
            [
                'action' => 'A',
                'noOfVehiclesRequired' => 10,
                'noOfTrailersRequired' => 10,
            ]
        ];

        $this->mockedSmServices['VariationOperatingCentreHelper']->shouldReceive('getListDataForApplication')
            ->with($application)
            ->andReturn($aocs);

        $this->repoMap['Application']->shouldReceive('save')
            ->with($application);

        $data = [
            'id' => 111,
            'section' => 'operatingCentres'
        ];
        $result = new Result();
        $result->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result);

        $this->expectedSideEffect(
            HandleOcVariationFees::class,
            $data = [
                'id' => 111,
            ],
            (new Result())->addMessage('HandleOcVariationFees')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Application record updated',
                'UpdateApplicationCompletion',
                'HandleOcVariationFees',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->references[EnforcementArea::class]['A111'], $licence->getEnforcementArea());
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSavesAVariationWithValidVehicleAuthorisations(): void
    {
        // Setup
        $this->setUpSut();
        $variation = ApplicationBuilder::variation()
            ->authorizedFor(static::ONE_HGV)
            ->withValidAuthorizations()
            ->build();
        $this->injectEntities($variation, ...$variation->getOperatingCentres());
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $variation->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthHgvVehicles(),
        ]);

        // Expect
        $this->applicationRepository()->expects('save')->withArgs(function ($application) {
            $this->assertInstanceOf(Application::class, $application);
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSavesAVariationWithValidVehicleAuthorisations')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSetsTotAuthVehiclesForGoodsVehicleOperatingCentre(): void
    {
        // Setup
        $this->setUpSut();
        $variation = ApplicationBuilder::variationForLicence(LicenceBuilder::aGoodsLicence())
            ->authorizedFor(static::ONE_HGV)
            ->withExtraOperatingCentreCapacityFor(static::ONE_HGV)
            ->build();
        $this->injectEntities($variation, ...$variation->getOperatingCentres());
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $variation->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $newCount = $variation->getTotAuthVehicles() + 1,
        ]);

        // Expect
        $this->applicationRepository()->expects('save')->withArgs(function (Application $application) use ($newCount) {
            $this->assertSame($newCount, $application->getTotAuthVehicles());
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSavesAVariationWithValidVehicleAuthorisations')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSetsTotAuthVehiclesForPsvOperatingCentre(): void
    {
        // Setup
        $this->setUpSut();
        $variation = ApplicationBuilder::variationForLicence(LicenceBuilder::aPsvLicence())
            ->authorizedFor(static::ONE_HGV)
            ->withExtraOperatingCentreCapacityFor(static::ONE_HGV)
            ->build();
        $this->injectEntities($variation, ...$variation->getOperatingCentres());
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $variation->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $newHgvTotal = $variation->getTotAuthVehicles() + 1,
        ]);

        // Expect
        $this->applicationRepository()->expects('save')->withArgs(function (Application $application) use ($newHgvTotal) {
            $this->assertSame($newHgvTotal, $application->getTotAuthVehicles());
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSavesAVariationWithValidVehicleAuthorisations')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSetsTotAuthHgvVehiclesForGoodsVehicleOperatingCentre(): void
    {
        // Setup
        $this->setUpSut();
        $variation = ApplicationBuilder::variationForLicence(LicenceBuilder::aGoodsLicence())
            ->authorizedFor(static::ONE_HGV)
            ->withExtraOperatingCentreCapacityFor(static::ONE_HGV)
            ->build();
        $this->injectEntities($variation, ...$variation->getOperatingCentres());
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $variation->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $newHgvTotal = $variation->getTotAuthVehicles() + 1,
        ]);

        // Expect
        $this->applicationRepository()->expects('save')->withArgs(function (Application $application) use ($newHgvTotal) {
            $this->assertSame($newHgvTotal, $application->getTotAuthHgvVehicles());
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSavesAVariationWithValidVehicleAuthorisations')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSetsTotAuthHgvVehiclesForPsvOperatingCentre(): void
    {
        // Setup
        $this->setUpSut();
        $variation = ApplicationBuilder::variationForLicence(LicenceBuilder::aPsvLicence())
            ->authorizedFor(static::ONE_HGV)
            ->withExtraOperatingCentreCapacityFor(static::ONE_HGV)
            ->build();
        $this->injectEntities($variation, ...$variation->getOperatingCentres());
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $variation->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $newHgvTotal = $variation->getTotAuthHgvVehicles() + 1,
        ]);

        // Expect
        $this->applicationRepository()->expects('save')->withArgs(function (Application $application) use ($newHgvTotal) {
            $this->assertSame($newHgvTotal, $application->getTotAuthHgvVehicles());
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSavesAVariationWithValidVehicleAuthorisations')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSetsTotalAuthLgvVehiclesForGoodsVehicleOperatingCentre(): void
    {
        // Setup
        $this->setUpSut();
        $variation = ApplicationBuilder::variationForLicence(LicenceBuilder::aGoodsLicence()->ofTypeStandardInternational())
            ->forMixedVehicleType()
            ->authorizedFor(static::ONE_HGV, static::ONE_LGV)
            ->withValidAuthorizations()
            ->build();
        $this->injectEntities($variation, ...$variation->getOperatingCentres());
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $variation->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthHgvVehicles(),
            static::TOT_AUTH_LGV_VEHICLES_COMMAND_PROPERTY => $newLgvTotal = $variation->getTotAuthLgvVehicles() + 1,
        ]);

        // Expect
        $this->applicationRepository()->expects('save')->withArgs(function (Application $application) use ($newLgvTotal) {
            $this->assertSame($newLgvTotal, $application->getTotAuthLgvVehicles());
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandDoesNotThrowValidationExceptionWhenCommunityLicencesAreEqualToTheTotalNumberOfVehicles(): void
    {
        // Setup
        $this->setUpSut();
        $variation = ApplicationBuilder::variationForLicence(LicenceBuilder::aGoodsLicence()->ofTypeStandardInternational())
            ->forMixedVehicleType()
            ->authorizedFor(static::ONE_HGV, static::TWO_LGVS)
            ->withValidAuthorizations()
            ->build();
        $this->injectEntities($variation, ...$variation->getOperatingCentres());
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $variation->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthHgvVehicles(),
            static::TOT_AUTH_LGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthLgvVehicles(),
            static::TOT_COMMUNITY_LICENCES_COMMAND_PROPERTY => $variation->getTotAuthVehicles(),
        ]);

        // Execute
        $this->sut->handleCommand($command);

        // Assert
        $this->assertTrue(true, 'Expected no validation exception to be thrown');
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandThrowsValidationExceptionWhenCommunityLicencesAreGreaterThenTheTotalNumberOfVehiclesAndApplicationHasStandardInternationalType(): void
    {
        // Setup
        $this->setUpSut();
        $variation = ApplicationBuilder::variationForLicence(LicenceBuilder::aGoodsLicence()->ofTypeStandardInternational())
            ->authorizedFor(static::ONE_HGV, static::ONE_LGV)
            ->withValidAuthorizations()
            ->build();
        $this->injectEntities($variation, ...$variation->getOperatingCentres());
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $variation->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthHgvVehicles(),
            static::TOT_AUTH_LGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthLgvVehicles(),
            static::TOT_COMMUNITY_LICENCES_COMMAND_PROPERTY => $variation->getTotAuthVehicles() + 1,
        ]);

        // Execute & Assert
        try {
            $this->sut->handleCommand($command);
        } catch (ValidationException $ex) {
            $this->assertEquals(
                static::COMMUNITY_LICENCE_COUNT_TOO_HIGH_VALIDATION_ERROR_CODE,
                $ex->getMessages()[static::TOT_COMMUNITY_LICENCES_COMMAND_PROPERTY][0]['ERR_OC_CL_1'] ?? null
            );
            return;
        }
        $this->assertTrue(false, 'Expected validation exception to be thrown');
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandThrowsValidationExceptionWhenCommunityLicencesAreGreaterThenTheTotalNumberOfVehiclesAndApplicationIsForPsvLicenceAndHasRestrictedType(): void
    {
        // Setup
        $this->setUpSut();
        $variation = ApplicationBuilder::variationForLicence(LicenceBuilder::aPsvLicence()->ofTypeRestricted())
            ->authorizedFor(static::ONE_HGV, static::ONE_LGV)
            ->withValidAuthorizations()
            ->build();
        $this->injectEntities($variation, ...$variation->getOperatingCentres());
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $variation->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthHgvVehicles(),
            static::TOT_AUTH_LGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthLgvVehicles(),
            static::TOT_COMMUNITY_LICENCES_COMMAND_PROPERTY => $variation->getTotAuthVehicles() + 1,
        ]);

        // Execute & Assert
        try {
            $this->sut->handleCommand($command);
        } catch (ValidationException $ex) {
            $this->assertEquals(
                static::COMMUNITY_LICENCE_COUNT_TOO_HIGH_VALIDATION_ERROR_CODE,
                $ex->getMessages()[static::TOT_COMMUNITY_LICENCES_COMMAND_PROPERTY][0][static::COMMUNITY_LICENCE_COUNT_TOO_HIGH_VALIDATION_ERROR_CODE] ?? null
            );
            return;
        }
        $this->assertTrue(false, 'Expected validation exception to be thrown');
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandThrowsValidationExceptionWithAnyValidationMessagesFromTheUpdateHelper(): void
    {
        // Setup
        $this->overrideUpdateHelperWithMock();
        $this->setUpSut();
        $variation = ApplicationBuilder::variationForLicence(LicenceBuilder::aGoodsLicence())->build();
        $this->injectEntities($variation);
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $variation->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthHgvVehicles(),
            static::TOT_AUTH_LGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthLgvVehicles(),
        ]);

        // Expect
        $this->updateHelper()->expects('getMessages')->andReturn($expectedMessages = static::VALIDATION_MESSAGES);
        $this->expectException(ValidationException::class);
        $this->expectExceptionObject(new ValidationException($expectedMessages));

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandValidatesPsvsWhenCommandIsNotPartial(): void
    {
        // Setup
        $this->overrideUpdateHelperWithMock();
        $this->setUpSut();
        $this->injectEntities($variation = ApplicationBuilder::variationForLicence(LicenceBuilder::aPsvLicence())->build());
        $command = Cmd::create([static::ID_COMMAND_PROPERTY => $variation->getId()]);

        // Expect
        $this->updateHelper()->expects('validatePsv')->withArgs(function ($arg1, $arg2) use ($variation, $command) {
            $this->assertSame($arg1, $variation);
            $this->assertSame($arg2, $command);
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandValidatesTotAuthHgvVehiclesWhenCommandIsNotPartial(): void
    {
        // Setup
        $this->overrideUpdateHelperWithMock();
        $this->setUpSut();
        $this->injectEntities($variation = ApplicationBuilder::variation()->build());
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $variation->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthHgvVehicles(),
            static::TOT_AUTH_LGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthLgvVehicles(),
        ]);

        // Expect
        $this->updateHelper()->expects('validateTotalAuthHgvVehicles')->withArgs(function ($arg1, $arg2, $arg3) use ($variation, $command) {
            $this->assertSame($arg1, $variation);
            $this->assertSame($arg2, $command);
            $this->assertIsArray($arg3);
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandValidatesTotAuthHgvVehiclesWhenCommandIsNotPartial')]
    #[\PHPUnit\Framework\Attributes\DataProvider('operatingCentreVehicleAuthorisationConstraintsDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandValidatesTotAuthHgvVehiclesWhenCommandIsNotPartialAgainstCorrectOperatingCentreVehicleConstraints(array $operatingCentresVehicleCapacities, array $expectedVehicleConstraints): void
    {
        // Setup
        $this->overrideUpdateHelperWithMock();
        $this->setUpSut();
        $variation = ApplicationBuilder::variation()->withOperatingCentresWithCapacitiesFor($operatingCentresVehicleCapacities)->build();
        $this->injectEntities($variation, ...$variation->getOperatingCentres());
        $command = Cmd::create([static::ID_COMMAND_PROPERTY => $variation->getId()]);

        // Expect
        $this->updateHelper()->expects('validateTotalAuthHgvVehicles')->withArgs(function ($arg1, $arg2, $arg3) use ($expectedVehicleConstraints, $operatingCentresVehicleCapacities) {
            $this->assertIsArray($arg3);
            foreach ($expectedVehicleConstraints as $key => $expectedTotal) {
                $this->assertSame(
                    $expectedTotal,
                    $actualTotal = $arg3[$key] ?? null,
                    sprintf('Failed to assert the value for "%s" total (%s) matched the expected value (%s)', $key, $actualTotal, $expectedTotal)
                );
            }
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandValidatesLgvsWhenCommandIsNotPartialAndApplicationIsForGoods(): void
    {
        // Setup
        $this->overrideUpdateHelperWithMock();
        $this->setUpSut();
        $this->injectEntities($variation = ApplicationBuilder::variationForLicence(LicenceBuilder::aGoodsLicence())->build());
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $variation->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthHgvVehicles(),
            static::TOT_AUTH_LGV_VEHICLES_COMMAND_PROPERTY => $variation->getTotAuthLgvVehicles(),
        ]);

        // Expect
        $this->updateHelper()->expects('validateTotalAuthLgvVehicles')->withArgs(function ($arg1, $arg2) use ($variation, $command) {
            $this->assertSame($arg1, $variation);
            $this->assertSame($arg2, $command);
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\Override]
    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new CommandHandler();

        if (null !== $this->serviceManager()) {
            $this->sut->__invoke($this->serviceManager(), CommandHandler::class);
        }
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $serviceManager->setService('TrafficAreaValidator', $this->setUpMockService(\Dvsa\Olcs\Api\Domain\Service\TrafficAreaValidator::class));
        $this->setUpAbstractCommandHandlerServices();
        $this->authService();
        $this->applicationRepository();
        $this->licenceOperatingCentreRepository();
        $this->applicationOperatingCentreRepository();
        $this->variationHelper();
        $this->updateHelper();
    }

    /**
     * @return m\MockInterface|AuthorizationService
     */
    protected function authService(): m\MockInterface
    {
        if (! $this->serviceManager()->has(AuthorizationService::class)) {
            $instance = $this->setUpMockService(AuthorizationService::class);
            $this->serviceManager()->setService(AuthorizationService::class, $instance);
        }
        return $this->serviceManager()->get(AuthorizationService::class);
    }

    /**
     * @return UpdateOperatingCentreHelper|m\MockInterface
     */
    protected function updateHelper(): mixed
    {
        if (! $this->serviceManager()->has('UpdateOperatingCentreHelper')) {
            $instance = new UpdateOperatingCentreHelper();
            $instance->__invoke($this->serviceManager(), null);
            $this->serviceManager()->setService('UpdateOperatingCentreHelper', $instance);
        }
        return $this->serviceManager()->get('UpdateOperatingCentreHelper');
    }

    /**
     * @return m\MockInterface|UpdateOperatingCentreHelper
     */
    protected function overrideUpdateHelperWithMock(): m\MockInterface
    {
        $instance = $this->setUpMockService(UpdateOperatingCentreHelper::class);
        $this->serviceManager()->setService('UpdateOperatingCentreHelper', $instance);
        return $this->serviceManager()->get('UpdateOperatingCentreHelper');
    }

    /**
     * @return VariationOperatingCentreHelper
     */
    protected function variationHelper(): VariationOperatingCentreHelper
    {
        if (! $this->serviceManager()->has('VariationOperatingCentreHelper')) {
            $instance = new VariationOperatingCentreHelper();
            $instance->__invoke($this->serviceManager(), null);
            $this->serviceManager()->setService('VariationOperatingCentreHelper', $instance);
        }
        return $this->serviceManager()->get('VariationOperatingCentreHelper');
    }

    /**
     * @deprecated Use new test format instead
     */
    protected function setUpLegacy(): void
    {
        $this->setUpSut();
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('ApplicationOperatingCentre', Repository\ApplicationOperatingCentre::class);
        $this->mockedSmServices['VariationOperatingCentreHelper'] = m::mock(VariationOperatingCentreHelper::class);
        $this->mockedSmServices['UpdateOperatingCentreHelper'] = $this->setUpMockService(UpdateOperatingCentreHelper::class);
        $this->mockedSmServices['TrafficAreaValidator'] =
            m::mock(\Dvsa\Olcs\Api\Domain\Service\TrafficAreaValidator::class);
        parent::setUp();
    }

    /**
     * @deprecated Use new test format instead
     */
    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [
            EnforcementArea::class => [
                'A111' => m::mock(EnforcementArea::class)
            ]
        ];

        parent::initReferences();
    }
}
