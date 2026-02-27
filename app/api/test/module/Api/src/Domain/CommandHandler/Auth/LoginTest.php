<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\Login;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Auth\Service\AuthenticationServiceInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Dvsa\OlcsTest\MocksServicesTrait;

/**
 * Class LoginTest
 * @see Login
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class LoginTest extends AbstractCommandHandlerTestCase
{
    use MocksServicesTrait;

    /** @var Login  */
    private $command;

    /**
     * @var AuthenticationServiceInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $mockAuthenticationService;

    /**
     * @var ValidatableAdapterInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $mockAdapter;

    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandIsCallable(): void
    {
        // Assert
        $this->assertIsCallable($this->sut->handleCommand(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandAdapterSetsUsernameAndPasswordFromCommand(): void
    {
        // Expectations
        $this->authenticationAdapter()->expects('setIdentity')->withArgs([$testUsername = 'testUsername']);
        $this->authenticationAdapter()->expects('setCredential')->withArgs([$testPassword = 'testPassword']);

        // Execute
        $this->sut->handleCommand(\Dvsa\Olcs\Transfer\Command\Auth\Login::create([
            'username' => $testUsername,
            'password' => $testPassword,
            'realm' => 'internal'
        ]));
    }


    #[\PHPUnit\Framework\Attributes\Depends('handleCommandAdapterSetsUsernameAndPasswordFromCommand')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandUpdatesUserLastLoginAtWhenAuthenticationIsSuccessful(): void
    {
        // Expectations
        $user = m::mock(User::class)->shouldIgnoreMissing()->byDefault();
        $user->expects('isInternal')
            ->twice()
            ->andReturn(true);
        $user->expects('getDeletedDate')
            ->andReturn(null);
        $user->expects('isDisabled')
            ->andReturn(false);
        $this->userRepository()
            ->expects('fetchByLoginId')
            ->withArgs(['testUsername'])
            ->andReturns([$user]);
        $this->userRepository()
            ->expects('updateLastLogin')
            ->withArgs([$user, m::type(\DateTime::class), $user]);

        // Execute
        $result = $this->sut->handleCommand(\Dvsa\Olcs\Transfer\Command\Auth\Login::create([
            'username' => 'testUsername',
            'password' => 'testPassword',
            'realm' => 'internal',
        ]));

        // Assert
        $this->assertInstanceOf(Result::class, $result);
        $this->assertNotNull($result->getFlag('isValid'));
        $this->assertNotNull($result->getFlag('code'));
        $this->assertNotNull($result->getFlag('identity'));
        $this->assertNotNull($result->getFlag('messages'));
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandUpdatesUserLastLoginAtWhenAuthenticationIsSuccessful')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandDoesNotUpdateLastLoginAtWhenAuthenticationIsNotSuccessful(): void
    {
        // Expectations
        $user = m::mock(User::class);
        $user->expects('getDeletedDate')
            ->andReturn(null);
        $user->expects('isDisabled')
            ->andReturn(false);
        $user->expects('isInternal')
            ->twice()
            ->andReturn(true);
        $user->expects('setLastLoginAt')->never();
        $this->userRepository()
            ->expects('fetchByLoginId')
            ->withArgs(['testUsername'])
            ->andReturns([$user]);

        $this->authenticationService()
            ->expects('authenticate')
            ->andReturns(
                new \Laminas\Authentication\Result(
                    \Laminas\Authentication\Result::FAILURE,
                    false
                )
            );

        // Execute
        $result = $this->sut->handleCommand(\Dvsa\Olcs\Transfer\Command\Auth\Login::create([
            'username' => 'testUsername',
            'password' => 'testPassword',
            'realm' => 'internal'
        ]));

        // Assert
        $this->assertInstanceOf(Result::class, $result);
        $this->assertNotNull($result->getFlag('isValid'));
        $this->assertNotNull($result->getFlag('code'));
        $this->assertEquals(\Laminas\Authentication\Result::FAILURE, $result->getFlag('code'));
        $this->assertNotNull($result->getFlag('identity'));
        $this->assertNotNull($result->getFlag('messages'));
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandDoesNotUpdateLastLoginAtWhenAuthenticationIsNotSuccessful')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandReturnsResultWithExpectedFlags(): void
    {
        // Execute
        $result = $this->sut->handleCommand(\Dvsa\Olcs\Transfer\Command\Auth\Login::create([
            'username' => 'testUsername',
            'password' => 'testPassword',
            'realm' => 'internal',
        ]));

        // Assert
        $this->assertInstanceOf(Result::class, $result);
        $this->assertNotNull($result->getFlag('isValid'));
        $this->assertNotNull($result->getFlag('code'));
        $this->assertNotNull($result->getFlag('identity'));
        $this->assertNotNull($result->getFlag('messages'));
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandAdapterSetsUsernameAndPasswordFromCommand')]
    #[\PHPUnit\Framework\Attributes\DataProvider('returnsExpectedResultWhenUserCannotAccessRealmProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandReturnsExpectedResultWhenUserCannotAccessRealm(bool $isInternal, string $realm): void
    {
        // Expectations
        $user = m::mock(User::class);
        $user->expects('isInternal')
            ->andReturn($isInternal);
        $user->expects('getDeletedDate')
            ->andReturn(null);
        $user->expects('isDisabled')
            ->andReturn(false);
        $user->expects('getLoginId')
            ->andReturn('testUsername');
        $this->userRepository()
            ->expects('fetchByLoginId')
            ->withArgs(['testUsername'])
            ->andReturns([$user]);

        // Execute
        $result = $this->sut->handleCommand(\Dvsa\Olcs\Transfer\Command\Auth\Login::create([
            'username' => 'testUsername',
            'password' => 'testPassword',
            'realm' => $realm
        ]));

        // Assert
        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->getFlag('isValid'));
        $this->assertEquals(\Laminas\Authentication\Result::FAILURE_CREDENTIAL_INVALID, $result->getFlag('code'));
        $this->assertEmpty($result->getFlag('identity'));

        $userRealm = $isInternal ? 'internal' : 'selfserve';
        $expectedMessage = sprintf('User with login_id "%s" with realm "%s" is attempting to log in to realm "%s"', 'testUsername', $userRealm, $realm);
        $this->assertEquals([$expectedMessage], $result->getFlag('messages'));
    }

    public static function returnsExpectedResultWhenUserCannotAccessRealmProvider(): array
    {
        return [
            'Internal user accessing selfserve' => [true, 'selfserve'],
            'Selfserve user accessing internal' => [false, 'internal'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandAdapterSetsUsernameAndPasswordFromCommand')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandReturnsExpectedResultWhenUserIsSelfServeWithMonitoredRolesAndHasNoOrg(): void
    {
        // Expectations
        $user = m::mock(User::class);
        $user->expects('isInternal')
            ->twice()
            ->andReturn(false);
        $user->expects('hasRoles')
            ->andReturn(true);
        $user->expects('getRelatedOrganisation')
            ->andReturn(null);
        $user->expects('getDeletedDate')
            ->andReturn(null);
        $user->expects('isDisabled')
            ->andReturn(false);
        $user->expects('getLoginId')
            ->andReturn('testUsername');
        $this->userRepository()
            ->expects('fetchByLoginId')
            ->withArgs(['testUsername'])
            ->andReturns([$user]);

        // Execute
        $result = $this->sut->handleCommand(\Dvsa\Olcs\Transfer\Command\Auth\Login::create([
            'username' => 'testUsername',
            'password' => 'testPassword',
            'realm' => 'selfserve'
        ]));

        // Assert
        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->getFlag('isValid'));
        $this->assertEquals(\Laminas\Authentication\Result::FAILURE_CREDENTIAL_INVALID, $result->getFlag('code'));
        $this->assertEmpty($result->getFlag('identity'));

        $expectedMessage = sprintf('User with login_id "%s" with selfserve realm has no organisation attached', 'testUsername');
        $this->assertEquals([$expectedMessage], $result->getFlag('messages'));
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandAdapterSetsUsernameAndPasswordFromCommand')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandReturnsExpectedResultWhenUserIsSelfServeWithoutMonitoredRolesAndHasNoOrg(): void
    {
        // Expectations
        $user = m::mock(User::class);
        $user->expects('isInternal')
            ->twice()
            ->andReturn(false);
        $user->expects('hasRoles')
            ->andReturn(false);
        $user->expects('getRelatedOrganisation')
            ->never();
        $user->expects('getDeletedDate')
            ->andReturn(null);
        $user->expects('isDisabled')
            ->andReturn(false);
        $this->userRepository()
            ->expects('fetchByLoginId')
            ->withArgs(['testUsername'])
            ->andReturns([$user]);
        $this->userRepository()
            ->expects('updateLastLogin')
            ->withArgs([$user, m::type(\DateTime::class), $user]);

        // Execute
        $result = $this->sut->handleCommand(\Dvsa\Olcs\Transfer\Command\Auth\Login::create([
            'username' => 'testUsername',
            'password' => 'testPassword',
            'realm' => 'selfserve'
        ]));

        // Assert
        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->getFlag('isValid'));
        $this->assertEquals(\Laminas\Authentication\Result::SUCCESS, $result->getFlag('code'));
        $this->assertNotEmpty($result->getFlag('identity'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandUserNotFoundInDatabaseReturnsNotFoundResult(): void
    {
        // Expectations
        $this->userRepository()
            ->expects('fetchByLoginId')
            ->withArgs(['testUsername'])
            ->andReturns([]);

        // Execute
        $result = $this->sut->handleCommand(\Dvsa\Olcs\Transfer\Command\Auth\Login::create([
            'username' => 'testUsername',
            'password' => 'testPassword',
            'realm' => 'internal'
        ]));

        // Assert
        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->getFlag('isValid'));
        $this->assertEquals(\Laminas\Authentication\Result::FAILURE_IDENTITY_NOT_FOUND, $result->getFlag('code'));
        $this->assertEmpty($result->getFlag('identity'));

        $expectedMessage = sprintf('User with login_id "%s" does not exist in the database.', 'testUsername');
        $this->assertEquals([$expectedMessage], $result->getFlag('messages'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandUserSoftDeletedInDatabaseReturnsNotFoundResult(): void
    {
        // Expectations
        $user = m::mock(User::class);
        $user->expects('getDeletedDate')
            ->andReturn(new \DateTime());
        $user->expects('getLoginId')
            ->andReturn('testUsername');
        $this->userRepository()
            ->expects('fetchByLoginId')
            ->withArgs(['testUsername'])
            ->andReturns([$user]);

        // Execute
        $result = $this->sut->handleCommand(\Dvsa\Olcs\Transfer\Command\Auth\Login::create([
            'username' => 'testUsername',
            'password' => 'testPassword',
            'realm' => 'internal'
        ]));

        // Assert
        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->getFlag('isValid'));
        $this->assertEquals(\Laminas\Authentication\Result::FAILURE_IDENTITY_NOT_FOUND, $result->getFlag('code'));
        $this->assertEmpty($result->getFlag('identity'));

        $expectedMessage = sprintf('User with login_id "%s" has been soft-deleted', 'testUsername');
        $this->assertEquals([$expectedMessage], $result->getFlag('messages'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandUserDisabledInDatabaseReturnsDisabledResult(): void
    {
        // Expectations
        $user = m::mock(User::class);
        $user->expects('getDeletedDate')
            ->andReturn(null);
        $user->expects('isDisabled')
            ->andReturn(true);
        $user->expects('getLoginId')
            ->andReturn('testUsername');
        $this->userRepository()
            ->expects('fetchByLoginId')
            ->withArgs(['testUsername'])
            ->andReturns([$user]);

        // Execute
        $result = $this->sut->handleCommand(\Dvsa\Olcs\Transfer\Command\Auth\Login::create([
            'username' => 'testUsername',
            'password' => 'testPassword',
            'realm' => 'internal'
        ]));

        // Assert
        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->getFlag('isValid'));
        $this->assertEquals(-5, $result->getFlag('code'));
        $this->assertEmpty($result->getFlag('identity'));

        $expectedMessage = sprintf('User with login_id "%s" is disabled', 'testUsername');
        $this->assertEquals([$expectedMessage], $result->getFlag('messages'));
    }

    public function setUp(): void
    {
        $this->setUpServiceManager();
        $this->setUpSut();

        parent::setUp();
    }

    protected function setUpSut(): void
    {
        $this->sut = new Login($this->authenticationService(), $this->authenticationAdapter());
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $this->userRepository();
        $this->authenticationService();
        $this->authenticationAdapter();
    }

    /**
     * @return UserRepo|m\MockInterface
     */
    protected function userRepository(): m\MockInterface
    {
        if (!array_key_exists('User', $this->repoMap)) {
            $repoMock = m::mock(UserRepo::class);
            $mockUser = m::mock(User::class)->shouldIgnoreMissing();
            $mockUser->allows('isDisabled')->andReturn(false)->byDefault();
            $repoMock->allows('fetchByLoginId')->andReturns([$mockUser])->byDefault();
            $repoMock->shouldIgnoreMissing();
            $this->repoMap['User'] = $repoMock;
        }

        return $this->repoMap['User'];
    }

    /**
     * @return AuthenticationServiceInterface|m\LegacyMockInterface|m\MockInterface
     */
    protected function authenticationService(): AuthenticationServiceInterface
    {
        if (!$this->serviceManager->has(AuthenticationServiceInterface::class)) {
            $authenticationService = m::mock(AuthenticationServiceInterface::class);
            $authenticationService->shouldIgnoreMissing();
            $authenticationService->allows('authenticate')->andReturns(new \Laminas\Authentication\Result(1, true))->byDefault();
            $this->serviceManager->setService(AuthenticationServiceInterface::class, $authenticationService);
        }

        return $this->serviceManager->get(AuthenticationServiceInterface::class);
    }

    /**
     * @return ValidatableAdapterInterface|m\LegacyMockInterface|m\MockInterface
     */
    protected function authenticationAdapter(string $adapterClass = ValidatableAdapterInterface::class): ValidatableAdapterInterface
    {
        if (!$this->serviceManager->has($adapterClass)) {
            $adapter = m::mock($adapterClass);
            $adapter->shouldIgnoreMissing();
            $adapter->allows('isValid')->andReturns(true)->byDefault();
            $adapter->allows('code')->andReturns(1)->byDefault();
            $adapter->allows('identity')->andReturns('user')->byDefault();
            $adapter->allows('getMessages')->andReturns(['Authentication was successful'])->byDefault();
            $this->serviceManager->setService($adapterClass, $adapter);
        }

        return $this->serviceManager->get($adapterClass);
    }
}
