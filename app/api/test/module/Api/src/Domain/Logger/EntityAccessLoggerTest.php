<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Logger;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\Logger\EntityAccessLogger;
use Dvsa\OlcsTest\MocksServicesTrait;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Mockery\MockInterface;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Rbac\Identity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Hamcrest\Core\IsAnything;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Audit\ReadOrganisation;
use Dvsa\Olcs\Transfer\Command\Audit\ReadLicence;
use Dvsa\Olcs\Transfer\Command\Audit\ReadCase;
use Dvsa\Olcs\Transfer\Command\Audit\ReadApplication;
use Dvsa\Olcs\Transfer\Command\Audit\ReadBusReg;
use Dvsa\Olcs\Transfer\Command\Audit\ReadTransportManager;
use Dvsa\Olcs\Transfer\Command\Audit\ReadIrhpApplication;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Doctrine\Common\Collections\ArrayCollection;
use DateTime;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use RuntimeException;

/**
 * @see EntityAccessLogger
 */
class EntityAccessLoggerTest extends MockeryTestCase
{
    use MocksServicesTrait;

    protected const USER_TYPE = 'USER TYPE';
    protected const USER_PID = 'USER PID';
    protected const ANONYMOUS_USER_PID = '';
    protected const IS_GRANTED = true;
    protected const NO_APPLICATION = null;
    protected const ECMS_NUMBER = 'ECMS NUMBER';
    protected const CASE_DESCRIPTION = 'CASE DESCRIPTION';
    protected const ENTITY_ID = 'ENTITY ID';
    protected const IS_NOT_VARIATION = false;
    protected const ENTITY_NOT_ENABLED_FOR_LOGGING_EXCEPTION_MESSAGE = 'Cannot create audit read for entity, no DTO is defined';

    /**
     * @var EntityAccessLogger
     */
    protected $sut;

    /**
     * @test
     */
    public function logAccessToEntityIsCallable()
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->logAccessToEntity(...));
    }

    /**
     * @test
     * @depends logAccessToEntityIsCallable
     */
    public function logAccessToEntityDoesNotLogAnythingWhenUserDoesNotHaveRequiredPermissionsAndReturnsFalse()
    {
        // Setup
        $this->setUpSut();
        $this->setUserContext($this->user());

        // Execute
        $result = $this->sut->logAccessToEntity($this->licence());

        // Assert
        $this->commandHandler()->shouldNotHaveReceived('handleCommand');
        $this->assertFalse($result);
    }

    /**
     * @return array
     */
    public function entitiesThatAreLoggedForUsersWithTheInternalUserPermissionDataProvider(): array
    {
        return [
            'organisation entity' => [$this->organisation(), ReadOrganisation::class],
            'licence entity' => [$this->licence(), ReadLicence::class],
            'case entity' => [$this->case(), ReadCase::class],
            'application entity' => [$this->application(), ReadApplication::class],
            'bus reg entity' => [$this->busRegistration(), ReadBusReg::class],
            'transport manager entity' => [$this->transportManager(), ReadTransportManager::class],
            'irhp application entity' => [$this->irhpApplication(), ReadIrhpApplication::class],
        ];
    }

    /**
     * @test
     * @depends logAccessToEntityIsCallable
     * @dataProvider entitiesThatAreLoggedForUsersWithTheInternalUserPermissionDataProvider
     */
    public function logAccessToEntityLogsEntryWhenUserHasInternalUserPermissionAndEntityIsEnabledForLoggingAndReturnsTrue(object $entity)
    {
        // Setup
        $this->setUpSut();
        $this->setUserContext($this->internalUser());
        $this->grantAllUsersPermission(Permission::INTERNAL_USER);

        // Execute
        $result = $this->sut->logAccessToEntity($entity);

        // Assert
        $this->assertThatAuditLogEntryWasMadeForEntity($entity);
        $this->assertTrue($result);
    }

    /**
     * @test
     * @depends logAccessToEntityIsCallable
     */
    public function logAccessToEntityThrowsExceptionWhenUserHasInternalUserPermissionAndEntityIsNotEnabledForLogging()
    {
        // Setup
        $this->setUpSut();
        $this->setUserContext($this->internalUser());
        $this->grantAllUsersPermission(Permission::INTERNAL_USER);

        // Expect
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(static::ENTITY_NOT_ENABLED_FOR_LOGGING_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->logAccessToEntity($this->entityThatIsNotEnabledForLogging());
    }

    /**
     * @test
     * @depends logAccessToEntityIsCallable
     */
    public function logAccessToEntityDoesNotLogAnythingWhenUserIsAnonymousAndHasInternalUserPermissionAndEntityIsEnabledForLoggingAndReturnsFalse()
    {
        // Setup
        $entity = array_values($this->entitiesThatAreLoggedForUsersWithTheInternalUserPermissionDataProvider())[0];
        $this->setUpSut();
        $this->setUserContext($this->anonymousUser());
        $this->grantAllUsersPermission(Permission::INTERNAL_USER);

        // Execute
        $result = $this->sut->logAccessToEntity($entity);

        // Assert
        $this->commandHandler()->shouldNotHaveReceived('handleCommand');
        $this->assertFalse($result);
    }

    /**
     * @test
     * @depends logAccessToEntityIsCallable
     */
    public function logAccessToEntityDoesNotLogAnythingWhenIdentityIsNotSetAndHasInternalUserPermissionAndEntityIsEnabledForLoggingAndReturnsFalse()
    {
        // Setup
        $entity = array_values($this->entitiesThatAreLoggedForUsersWithTheInternalUserPermissionDataProvider())[0];
        $this->setUpSut();
        $this->setUserContext(null);
        $this->grantAllUsersPermission(Permission::INTERNAL_USER);

        // Execute
        $result = $this->sut->logAccessToEntity($entity);

        // Assert
        $this->commandHandler()->shouldNotHaveReceived('handleCommand');
        $this->assertFalse($result);
    }

    /**
     * @return array
     */
    public function entitiesThatAreLoggedForUsersWithThePartnerUserPermissionDataProvider(): array
    {
        return [
            'licence entity' => [$this->licence(), ReadLicence::class],
        ];
    }

    /**
     * @test
     * @depends logAccessToEntityIsCallable
     * @dataProvider entitiesThatAreLoggedForUsersWithThePartnerUserPermissionDataProvider
     */
    public function logAccessToEntityLogsEntryWhenUserHasPartnerUserPermissionAndEntityIsEnabledForLoggingAndReturnsTrue(object $entity)
    {
        // Setup
        $this->setUpSut();
        $this->setUserContext($this->partnerUser());
        $this->grantAllUsersPermission(Permission::PARTNER_USER);

        // Execute
        $result = $this->sut->logAccessToEntity($entity);

        // Assert
        $this->assertThatAuditLogEntryWasMadeForEntity($entity);
        $this->assertTrue($result);
    }

    /**
     * @return array
     */
    public function entitiesThatHaveNotBeenEnabledForUsersThatHavePartnerUserPermissionDataProvider(): array
    {
        return [
            'organisation entity' => [$this->organisation(), ReadOrganisation::class],
            'case entity' => [$this->case(), ReadCase::class],
            'application entity' => [$this->application(), ReadApplication::class],
            'bus reg entity' => [$this->busRegistration(), ReadBusReg::class],
            'transport manager entity' => [$this->transportManager(), ReadTransportManager::class],
            'irhp application entity' => [$this->irhpApplication(), ReadIrhpApplication::class],
        ];
    }

    /**
     * @test
     * @depends logAccessToEntityIsCallable
     * @dataProvider entitiesThatHaveNotBeenEnabledForUsersThatHavePartnerUserPermissionDataProvider
     */
    public function logAccessToEntityThrowsExceptionWhenUserHasPartnerUserPermissionAndEntityIsNotEnabledForLogging(object $entity)
    {
        // Setup
        $this->setUpSut();
        $this->setUserContext($this->internalUser());
        $this->grantAllUsersPermission(Permission::PARTNER_USER);

        // Expect
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(static::ENTITY_NOT_ENABLED_FOR_LOGGING_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->logAccessToEntity($entity);
    }

    /**
     * @return array
     */
    public function entitiesThatAreLoggedForUsersWithThePartnerAdminPermissionDataProvider(): array
    {
        return [
            'licence entity' => [$this->licence(), ReadLicence::class],
        ];
    }

    /**
     * @test
     * @depends logAccessToEntityIsCallable
     * @dataProvider entitiesThatAreLoggedForUsersWithThePartnerAdminPermissionDataProvider
     */
    public function logAccessToEntityLogsEntryWhenUserHasPartnerAdminPermissionAndEntityIsEnabledForLoggingAndReturnsTrue(object $entity)
    {
        // Setup
        $this->setUpSut();
        $this->setUserContext($this->partnerUser());
        $this->grantAllUsersPermission(Permission::PARTNER_ADMIN);

        // Execute
        $result = $this->sut->logAccessToEntity($entity);

        // Assert
        $this->assertThatAuditLogEntryWasMadeForEntity($entity);
        $this->assertTrue($result);
    }

    /**
     * @return array
     */
    public function entitiesThatHaveNotBeenEnabledForUsersThatHavePartnerAdminPermissionDataProvider(): array
    {
        return [
            'organisation entity' => [$this->organisation(), ReadOrganisation::class],
            'case entity' => [$this->case(), ReadCase::class],
            'application entity' => [$this->application(), ReadApplication::class],
            'bus reg entity' => [$this->busRegistration(), ReadBusReg::class],
            'transport manager entity' => [$this->transportManager(), ReadTransportManager::class],
            'irhp application entity' => [$this->irhpApplication(), ReadIrhpApplication::class],
        ];
    }

    /**
     * @test
     * @depends logAccessToEntityIsCallable
     * @dataProvider entitiesThatHaveNotBeenEnabledForUsersThatHavePartnerAdminPermissionDataProvider
     */
    public function logAccessToEntityThrowsExceptionWhenUserHasPartnerAdminPermissionAndEntityIsNotEnabledForLogging(object $entity)
    {
        // Setup
        $this->setUpSut();
        $this->setUserContext($this->internalUser());
        $this->grantAllUsersPermission(Permission::PARTNER_ADMIN);

        // Expect
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(static::ENTITY_NOT_ENABLED_FOR_LOGGING_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->logAccessToEntity($entity);
    }

    protected function setUp(): void
    {
        $this->setUpServiceManager();
    }

    public function setUpSut(): void
    {
        $this->sut = new EntityAccessLogger($this->authorizationService(), $this->commandHandler());
    }

    /**
     * @return MockInterface|AuthorizationService
     */
    protected function authorizationService(): MockInterface
    {
        if (! $this->serviceManager->has(AuthorizationService::class)) {
            $instance = $this->setUpMockService(AuthorizationService::class);
            $this->serviceManager->setService(AuthorizationService::class, $instance);
        }
        return $this->serviceManager->get(AuthorizationService::class);
    }

    /**
     * @return MockInterface|CommandHandlerManager
     */
    protected function commandHandler(): MockInterface
    {
        if (! $this->serviceManager->has(CommandHandlerManager::class)) {
            $instance = $this->setUpMockService(CommandHandlerManager::class);
            $this->serviceManager->setService(CommandHandlerManager::class, $instance);
        }
        return $this->serviceManager->get(CommandHandlerManager::class);
    }

    /**
     * @return Licence
     */
    protected function licence(): Licence
    {
        $entity = new Licence($this->organisation(), $this->notSubmittedLicenceStatus());
        $entity->setId(static::ENTITY_ID);
        return $entity;
    }

    /**
     * @return Organisation
     */
    protected function organisation(): Organisation
    {
        $entity = new Organisation();
        $entity->setId(static::ENTITY_ID);
        return $entity;
    }

    /**
     * @return TransportManager
     */
    protected function transportManager(): TransportManager
    {
        $entity = new TransportManager();
        $entity->setId(static::ENTITY_ID);
        return $entity;
    }

    /**
     * @return Application
     */
    protected function application(): Application
    {
        $entity = new Application(
            $this->licence(),
            new RefData(Application::APPLICATION_STATUS_NOT_SUBMITTED),
            static::IS_NOT_VARIATION
        );
        $entity->setId(static::ENTITY_ID);
        return $entity;
    }

    /**
     * @return BusReg
     */
    protected function busRegistration(): BusReg
    {
        $entity = new BusReg();
        $entity->setId(static::ENTITY_ID);
        return $entity;
    }

    /**
     * @return IrhpApplication
     */
    protected function irhpApplication(): IrhpApplication
    {
        $entity = new IrhpApplication();
        $entity->setId(static::ENTITY_ID);
        return $entity;
    }

    /**
     * @return Cases
     */
    protected function case(): Cases
    {
        $entity = new Cases(
            new DateTime(),
            new RefData(Cases::LICENCE_CASE_TYPE),
            new ArrayCollection(),
            new ArrayCollection(),
            static::NO_APPLICATION,
            $this->licence(),
            $this->transportManager(),
            static::ECMS_NUMBER,
            static::CASE_DESCRIPTION
        );
        $entity->setId(static::ENTITY_ID);
        return $entity;
    }

    /**
     * @return RefData
     */
    protected function notSubmittedLicenceStatus(): RefData
    {
        return new RefData(Licence::LICENCE_STATUS_NOT_SUBMITTED);
    }

    /**
     * @return User
     */
    protected function entityThatIsNotEnabledForLogging(): User
    {
        return $this->user();
    }

    /**
     * @param User|null $user
     */
    protected function setUserContext(User $user = null)
    {
        $identity = null === $user ? null : new Identity($user);
        $this->authorizationService()->allows('getIdentity')->andReturn($identity)->byDefault();
    }

    /**
     * @return User
     */
    protected function user(): User
    {
        return new User(static::USER_PID, static::USER_TYPE);
    }

    /**
     * @return User
     */
    protected function anonymousUser(): User
    {
        $entity = new User(static::ANONYMOUS_USER_PID, User::USER_TYPE_ANON);
        $entity->setPid(static::ANONYMOUS_USER_PID);
        $entity->setLoginId(User::ANON_USERNAME);
        return $entity;
    }

    /**
     * @return User
     */
    protected function internalUser(): User
    {
        $entity = new User(static::USER_PID, User::USER_TYPE_INTERNAL);
        $entity->setLoginId('loginId');
        return $entity;
    }

    /**
     * @return User
     */
    protected function partnerUser(): User
    {
        $entity = new User(static::USER_PID, User::USER_TYPE_PARTNER);
        $entity->setLoginId('loginId');
        return $entity;
    }

    protected function grantAllUsersPermission(string $permission)
    {
        $this->authorizationService()->allows('isGranted')->with($permission)->andReturn(static::IS_GRANTED)->byDefault();
    }

    protected function assertThatAuditLogEntryWasMadeForEntity(object $entity)
    {
        $this->commandHandler()->shouldHaveReceived('handleCommand')->withArgs(function ($command) use ($entity) {
            $this->assertInstanceOf(EntityAccessLogger::ENTITY_AUDIT_LOG_COMMAND_MAP[$entity::class], $command);
            $this->assertSame($entity->getId(), $command->getId());
            return true;
        });
    }
}
