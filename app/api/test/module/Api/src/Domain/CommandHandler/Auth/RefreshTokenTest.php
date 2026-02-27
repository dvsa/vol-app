<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\RefreshTokens;
use Dvsa\Olcs\Api\Domain\Repository\User;
use Dvsa\Olcs\Auth\Service\AuthenticationServiceInterface;
use Dvsa\Olcs\Transfer\Command\Auth\RefreshTokens as RefreshTokenCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\MocksAbstractCommandHandlerServicesTrait;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\Authentication\Result;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\MockInterface;
use Dvsa\OlcsTest\MocksServicesTrait;
use LmcRbacMvc\Identity\IdentityInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see RefreshTokens
 */
class RefreshTokenTest extends AbstractCommandHandlerTestCase
{
    use MocksServicesTrait;
    use MocksAbstractCommandHandlerServicesTrait;

    /** @var RefreshTokens  */
    private $command;

    /**
     * @var AuthenticationServiceInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $mockAuthenticationService;

    /**
     * @var ValidatableAdapterInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $mockAdapter;

    protected $sut;

    private string $username = 'username';
    private string $token = 'token';
    private string $refreshedToken = 'new_token';

    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->handleCommand(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleCommandIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleCommandReturnsExpectedResult(): void
    {
        // Setup
        $this->setUpSut();

        $adapter = $this->adapter();
        $adapter->shouldReceive('refreshToken')
            ->with($this->token, $this->username)
            ->andReturn(new Result(1, $this->identity(), [], []));

        // Execute
        $result = $this->sut->handleCommand(RefreshTokenCommand::create([
            'refreshToken' => $this->token,
            'username' => $this->username
        ]));

        // Expectations
        $expectedResult = [
            'id' => [],
            'messages' => [],
            'flags' => [
                'isValid' => true,
                'code' => 1,
                'identity' => $this->identity(),
                'messages' => []
            ]
        ];

        $this->assertSame($result->toArray(), $expectedResult);
    }

    #[\Override]
    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new RefreshTokens($this->adapter());

        if (null !== $this->serviceManager()) {
            $this->sut->__invoke($this->serviceManager, null);
        }
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $this->adapter();
        $this->authService();
        $this->userRepository();
        $this->setUpAbstractCommandHandlerServices();
    }

    /**
     * @return ValidatableAdapterInterface|m\LegacyMockInterface|m\MockInterface
     */
    protected function adapter(): ValidatableAdapterInterface
    {
        if (!$this->serviceManager->has(ValidatableAdapterInterface::class)) {
            $adapter = m::mock(ValidatableAdapterInterface::class);
            $adapter->shouldIgnoreMissing();
            $this->serviceManager->setService(ValidatableAdapterInterface::class, $adapter);
        }

        return $this->serviceManager->get(ValidatableAdapterInterface::class);
    }

    /**
     * @return m\MockInterface|AuthorizationService
     */
    protected function authService(): m\MockInterface
    {
        if (! $this->serviceManager()->has(AuthorizationService::class)) {
            $instance = $this->setUpMockService(AuthorizationService::class);

            $mockUser = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class);
            $mockUser->allows('getLoginId')->withNoArgs()->andReturn($this->username);

            $identity = m::mock(IdentityInterface::class);
            $identity->allows('getUser')->withNoArgs()->andReturn($mockUser);

            $instance->allows('getIdentity')->withNoArgs()->andReturn($identity);

            $this->serviceManager()->setService(AuthorizationService::class, $instance);
        }
        return $this->serviceManager()->get(AuthorizationService::class);
    }

    /**
     * @return MockInterface|User
     */
    protected function userRepository(): mixed
    {
        $repositoryServiceManager = $this->repositoryServiceManager();
        if (! $repositoryServiceManager->has('User')) {
            $instance = $this->setUpMockService(User::class);
            $repositoryServiceManager->setService('User', $instance);
        }
        return $repositoryServiceManager->get('User');
    }

    protected function identity(): array
    {
        return [
            'Provider' => 'Client',
            'Token' => $this->refreshedToken,
            'ResourceOwner' => 'resourceOwner',
            'AccessToken' => 'accessToken',
            'AccessTokenClaims' => 'accessTokenClaims',
            'IdToken' => 'idToken',
            'IdTokenClaims' => 'idTokenClaims',
            'RefreshToken' => 'refreshToken'
        ];
    }
}
