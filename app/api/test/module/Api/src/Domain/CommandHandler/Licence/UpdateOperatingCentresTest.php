<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UpdateOperatingCentres as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Service\UpdateOperatingCentreHelper;
use Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateOperatingCentres as Cmd;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\Application\ProvidesOperatingCentreVehicleAuthorizationConstraintsTrait;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\MocksAbstractCommandHandlerServicesTrait;
use Dvsa\OlcsTest\Api\Domain\Repository\MocksLicenceRepositoryTrait;
use Dvsa\OlcsTest\Api\Entity\Licence\LicenceBuilder;
use Dvsa\OlcsTest\MocksServicesTrait;
use Hamcrest\Arrays\IsArrayContainingKeyValuePair;
use Hamcrest\Core\AllOf;
use Laminas\ServiceManager\ServiceManager;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * @see \Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UpdateOperatingCentres
 */
class UpdateOperatingCentresTest extends AbstractCommandHandlerTestCase
{
    use MocksServicesTrait;
    use MocksAbstractCommandHandlerServicesTrait;
    use ProvidesOperatingCentreVehicleAuthorizationConstraintsTrait;
    use MocksLicenceRepositoryTrait;

    protected const TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY = 'totAuthHgvVehicles';
    protected const TOT_AUTH_LGV_VEHICLES_COMMAND_PROPERTY = 'totAuthLgvVehicles';
    protected const ID_COMMAND_PROPERTY = 'id';
    protected const AN_ID = 1;
    protected const VALIDATION_MESSAGES = ['A VALIDATION MESSAGE KEY' => 'A VALIDATION MESSAGE VALUE'];
    protected const ONE_HGV = 1;
    protected const ONE_LGV = 1;

    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->handleCommand(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    public function testHandleCommandPsvInvalid(): void
    {
        $this->setUpLegacy();
        $data = [
            'id' => 111,
            'version' => 1,
            'partial' => false
        ];
        $command = Cmd::create($data);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc->setNoOfVehiclesRequired(10);
        $loc->setNoOfTrailersRequired(10);

        $locs = new ArrayCollection();
        $locs->add($loc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('isPsv')->andReturn(true);
        $licence->setOperatingCentres($locs);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licence);

        $expectedTotals = AllOf::allOf(
            IsArrayContainingKeyValuePair::hasKeyValuePair('noOfOperatingCentres', 1),
            IsArrayContainingKeyValuePair::hasKeyValuePair('minTrailerAuth', 10),
            IsArrayContainingKeyValuePair::hasKeyValuePair('maxTrailerAuth', 10)
        );

        $this->mockedSmServices['UpdateOperatingCentreHelper']->shouldReceive('validatePsv')
            ->once()
            ->with($licence, $command)
            ->shouldReceive('validateTotalAuthHgvVehicles')
            ->once()
            ->with($licence, $command, $expectedTotals)
            ->shouldReceive('validateEnforcementArea')
            ->once()
            ->with($licence, $command)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn(['foo' => 'bar']);

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
            'partial' => false
        ];
        $command = Cmd::create($data);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc->setNoOfVehiclesRequired(10);
        $loc->setNoOfTrailersRequired(10);

        $locs = new ArrayCollection();
        $locs->add($loc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('isPsv')->andReturn(false);
        $licence->setOperatingCentres($locs);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licence);

        $expectedTotals = AllOf::allOf(
            IsArrayContainingKeyValuePair::hasKeyValuePair('noOfOperatingCentres', 1),
            IsArrayContainingKeyValuePair::hasKeyValuePair('minTrailerAuth', 10),
            IsArrayContainingKeyValuePair::hasKeyValuePair('maxTrailerAuth', 10)
        );

        $this->mockedSmServices['UpdateOperatingCentreHelper']
            ->shouldIgnoreMissing()
            ->shouldReceive('validateTotalAuthTrailers')
            ->once()
            ->with($licence, $command, $expectedTotals)
            ->shouldReceive('validateTotalAuthHgvVehicles')
            ->once()
            ->with($licence, $command, $expectedTotals)
            ->shouldReceive('validateEnforcementArea')
            ->once()
            ->with($licence, $command)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn(['foo' => 'bar']);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    public function testHandleCommandPsvValid(): void
    {
        $this->setUpLegacy();
        $data = [
            'id' => 111,
            'version' => 1,
            'partial' => false,
            'totAuthSmallVehicles' => 3,
            'totAuthMediumVehicles' => 3,
            'totAuthLargeVehicles' => 4,
            'totAuthVehicles' => 10,
            ''
        ];
        $command = Cmd::create($data);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc->setNoOfVehiclesRequired(10);
        $loc->setNoOfTrailersRequired(10);

        $locs = new ArrayCollection();
        $locs->add($loc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('isPsv')->andReturn(true);
        $licence->shouldReceive('canHaveLargeVehicles')->andReturn(true);
        $licence->setOperatingCentres($locs);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licence);

        $expectedTotals = AllOf::allOf(
            IsArrayContainingKeyValuePair::hasKeyValuePair('noOfOperatingCentres', 1),
            IsArrayContainingKeyValuePair::hasKeyValuePair('minTrailerAuth', 10),
            IsArrayContainingKeyValuePair::hasKeyValuePair('maxTrailerAuth', 10)
        );

        $this->mockedSmServices['UpdateOperatingCentreHelper']->shouldReceive('validatePsv')
            ->once()
            ->with($licence, $command)
            ->shouldReceive('validateTotalAuthHgvVehicles')
            ->once()
            ->with($licence, $command, $expectedTotals)
            ->shouldReceive('validateEnforcementArea')
            ->once()
            ->with($licence, $command)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn([]);

        $this->repoMap['Licence']->shouldReceive('save')
            ->once()
            ->with($licence);

        $this->expectedLicenceCacheClear($licence);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ['Licence record updated']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    public function testHandleCommandGvValid(): void
    {
        $this->setUpLegacy();
        $data = [
            'id' => 111,
            'version' => 1,
            'partial' => false,
            'totAuthVehicles' => 10,
            'totAuthTrailers' => 10,
            'enforcementArea' => 'A111'
        ];
        $command = Cmd::create($data);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc->setNoOfVehiclesRequired(10);
        $loc->setNoOfTrailersRequired(10);

        $locs = new ArrayCollection();
        $locs->add($loc);

        $ta = m::mock(TrafficArea::class)->makePartial();

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('isPsv')->andReturn(false);
        $licence->setOperatingCentres($locs);
        $licence->setTrafficArea($ta);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licence);

        $expectedTotals = AllOf::allOf(
            IsArrayContainingKeyValuePair::hasKeyValuePair('noOfOperatingCentres', 1),
            IsArrayContainingKeyValuePair::hasKeyValuePair('minTrailerAuth', 10),
            IsArrayContainingKeyValuePair::hasKeyValuePair('maxTrailerAuth', 10)
        );

        $this->mockedSmServices['UpdateOperatingCentreHelper']
            ->shouldIgnoreMissing()
            ->shouldReceive('validateTotalAuthTrailers')
            ->once()
            ->with($licence, $command, $expectedTotals)
            ->shouldReceive('validateTotalAuthHgvVehicles')
            ->once()
            ->with($licence, $command, $expectedTotals)
            ->shouldReceive('validateEnforcementArea')
            ->once()
            ->with($licence, $command)
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn([]);

        $this->repoMap['Licence']->shouldReceive('save')
            ->once()
            ->with($licence);

        $this->expectedLicenceCacheClear($licence);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ['Licence record updated']
        ];

        $this->assertEquals($expected, $result->toArray());
        $this->assertSame($this->references[EnforcementArea::class]['A111'], $licence->getEnforcementArea());
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSavesALicenceWithValidVehicleAuthorizations(): void
    {
        // Setup
        $this->setUpSut();
        $licence = LicenceBuilder::aLicence()
            ->forMixedVehicleType()
            ->withValidVehicleAuthorizations()
            ->build();
        $this->injectEntities($licence);
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $licence->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $licence->getTotAuthHgvVehicles(),
            static::TOT_AUTH_LGV_VEHICLES_COMMAND_PROPERTY => $licence->getTotAuthLgvVehicles(),
        ]);

        // Expect
        $this->licenceRepository()->expects('save')->withArgs(function ($licence) {
            $this->assertInstanceOf(Licence::class, $licence);
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSavesALicenceWithValidVehicleAuthorizations')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSetsTotAuthHgvVehiclesForGoodsVehicleOperatingCentre(): void
    {
        // Setup
        $this->setUpSut();
        $licence = LicenceBuilder::aLicence()->withExtraOperatingCentreCapacityFor(static::ONE_HGV)->build();
        $this->injectEntities($licence);
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $licence->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $expectedVehicleCount = $licence->getTotAuthHgvVehicles() + static::ONE_HGV,
        ]);

        // Expect
        $this->licenceRepository()->expects('save')->withArgs(function (Licence $licence) use ($expectedVehicleCount) {
            $this->assertSame($expectedVehicleCount, $licence->getTotAuthHgvVehicles());
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSavesALicenceWithValidVehicleAuthorizations')]
    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSetsTotAuthHgvVehiclesForGoodsVehicleOperatingCentre')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSetsTotAuthVehiclesForGoodsVehicleOperatingCentre(): void
    {
        // Setup
        $this->setUpSut();
        $licence = LicenceBuilder::aLicence()->withExtraOperatingCentreCapacityFor(static::ONE_HGV)->build();
        $this->injectEntities($licence);
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $licence->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $expectedVehicleCount = $licence->getTotAuthHgvVehicles() + static::ONE_HGV,
        ]);

        // Expect
        $this->licenceRepository()->expects('save')->withArgs(function (Licence $licence) use ($expectedVehicleCount) {
            $this->assertSame($expectedVehicleCount, $licence->getTotAuthVehicles());
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSavesALicenceWithValidVehicleAuthorizations')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSetsTotAuthHgvVehiclesForPsvOperatingCentre(): void
    {
        // Setup
        $this->setUpSut();
        $licence = LicenceBuilder::aPsvLicence()->withExtraOperatingCentreCapacityFor(static::ONE_HGV)->build();
        $this->injectEntities($licence);
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $licence->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $expectedVehicleCount = $licence->getTotAuthHgvVehicles() + static::ONE_HGV,
        ]);

        // Expect
        $this->licenceRepository()->expects('save')->withArgs(function (Licence $licence) use ($expectedVehicleCount) {
            $this->assertSame($expectedVehicleCount, $licence->getTotAuthHgvVehicles());
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSavesALicenceWithValidVehicleAuthorizations')]
    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSetsTotAuthHgvVehiclesForPsvOperatingCentre')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSetsTotAuthVehiclesForPsvOperatingCentre(): void
    {
        // Setup
        $this->setUpSut();
        $licence = LicenceBuilder::aPsvLicence()->withExtraOperatingCentreCapacityFor(static::ONE_HGV)->build();
        $this->injectEntities($licence);
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $licence->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $expectedVehicleCount = $licence->getTotAuthHgvVehicles() + static::ONE_HGV,
        ]);

        // Expect
        $this->licenceRepository()->expects('save')->withArgs(function (Licence $licence) use ($expectedVehicleCount) {
            $this->assertSame($expectedVehicleCount, $licence->getTotAuthVehicles());
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSavesALicenceWithValidVehicleAuthorizations')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSetsTotalAuthLgvVehiclesForGoodsVehicleOperatingCentre(): void
    {
        // Setup
        $this->setUpSut();
        $licence = LicenceBuilder::aGoodsLicence()
            ->ofTypeStandardInternational()
            ->forMixedVehicleType()
            ->withValidVehicleAuthorizations()
            ->build();
        $this->injectEntities($licence);
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $licence->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $licence->getTotAuthHgvVehicles(),
            static::TOT_AUTH_LGV_VEHICLES_COMMAND_PROPERTY => $expectedVehicleCount = $licence->getTotAuthLgvVehicles() + static::ONE_LGV,
        ]);

        // Expect
        $this->licenceRepository()->expects('save')->withArgs(function (Licence $licence) use ($expectedVehicleCount) {
            $this->assertSame($expectedVehicleCount, $licence->getTotAuthLgvVehicles());
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSavesALicenceWithValidVehicleAuthorizations')]
    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSetsTotAuthHgvVehiclesForGoodsVehicleOperatingCentre')]
    #[\PHPUnit\Framework\Attributes\Depends('handleCommandSetsTotalAuthLgvVehiclesForGoodsVehicleOperatingCentre')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandSetsTotAuthVehiclesForGoodsVehicleOperatingCentreWithHgvsAndLgvs(): void
    {
        // Setup
        $this->setUpSut();
        $licence = LicenceBuilder::aGoodsLicence()
            ->ofTypeStandardInternational()
            ->forMixedVehicleType()
            ->withExtraOperatingCentreCapacityFor(static::ONE_HGV)
            ->build();
        $this->injectEntities($licence);
        $command = Cmd::create([
            static::ID_COMMAND_PROPERTY => $licence->getId(),
            static::TOT_AUTH_HGV_VEHICLES_COMMAND_PROPERTY => $expectedHgvCount = $licence->getTotAuthHgvVehicles() + static::ONE_HGV,
            static::TOT_AUTH_LGV_VEHICLES_COMMAND_PROPERTY => $expectedLgvCount = $licence->getTotAuthLgvVehicles() + static::ONE_LGV,
        ]);

        // Expect
        $this->licenceRepository()->expects('save')->withArgs(function (Licence $licence) use ($expectedHgvCount, $expectedLgvCount) {
            $this->assertSame($expectedHgvCount + $expectedLgvCount, $licence->getTotAuthVehicles());
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandThrowsValidationExceptionWithAnyValidationMessagesFromTheUpdateHelper(): void
    {
        // Setup
        $this->overrideUpdateHelperWithMock();
        $this->setUpSut();
        $this->injectEntities(LicenceBuilder::aPsvLicence($id = static::AN_ID));
        $command = Cmd::create([static::ID_COMMAND_PROPERTY => $id]);

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
        $this->injectEntities($licence = LicenceBuilder::aPsvLicence()->build());
        $command = Cmd::create([static::ID_COMMAND_PROPERTY => $licence->getId()]);

        // Expect
        $this->updateHelper()->expects('validatePsv')->withArgs(function ($arg1, $arg2) use ($licence, $command) {
            $this->assertSame($arg1, $licence);
            $this->assertSame($arg2, $command);
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandValidatesHgvsWhenCommandIsNotPartial(): void
    {
        // Setup
        $this->overrideUpdateHelperWithMock();
        $this->setUpSut();
        $this->injectEntities($licence = LicenceBuilder::aLicence()->build());
        $command = Cmd::create([static::ID_COMMAND_PROPERTY => $licence->getId()]);

        // Expect
        $this->updateHelper()->expects('validateTotalAuthHgvVehicles')->withArgs(function ($arg1, $arg2, $arg3) use ($licence, $command) {
            $this->assertSame($arg1, $licence);
            $this->assertSame($arg2, $command);
            $this->assertIsArray($arg3);
            return true;
        });

        // Execute
        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandValidatesHgvsWhenCommandIsNotPartial')]
    #[\PHPUnit\Framework\Attributes\DataProvider('operatingCentreVehicleAuthorisationConstraintsDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandValidatesHgvsWhenCommandIsNotPartialAgainstCorrectOperatingCentreVehicleConstraints(array $operatingCentresVehicleCapacities, array $expectedVehicleConstraints): void
    {
        // Setup
        $this->overrideUpdateHelperWithMock();
        $this->setUpSut();
        $licence = LicenceBuilder::aLicence(static::AN_ID)->withOperatingCentresWithCapacitiesFor($operatingCentresVehicleCapacities)->build();
        $this->injectEntities($licence);
        $command = Cmd::create([static::ID_COMMAND_PROPERTY => $licence->getId()]);

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
    public function handleCommandValidatesLgvsWhenCommandIsNotPartialAndLicenceIsForGoods(): void
    {
        // Setup
        $this->overrideUpdateHelperWithMock();
        $this->setUpSut();
        $this->injectEntities($licence = LicenceBuilder::aGoodsLicence()->build());
        $command = Cmd::create([static::ID_COMMAND_PROPERTY => $licence->getId()]);

        // Expect
        $this->updateHelper()->expects('validateTotalAuthLgvVehicles')->withArgs(function ($arg1, $arg2) use ($licence, $command) {
            $this->assertSame($arg1, $licence);
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
        $this->authService();
        $this->updateHelper();
        $this->setUpAbstractCommandHandlerServices();
        $this->licenceRepository();
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
     * @deprecated Use new test format
     */
    protected function setUpLegacy(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('LicenceOperatingCentre', Repository\LicenceOperatingCentre::class);

        $this->mockedSmServices['UpdateOperatingCentreHelper'] = m::mock(UpdateOperatingCentreHelper::class);
        $this->mockedSmServices[CacheEncryption::class] = m::mock(CacheEncryption::class);

        parent::setUp();
    }

    /**
     * @deprecated Use new test format
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
