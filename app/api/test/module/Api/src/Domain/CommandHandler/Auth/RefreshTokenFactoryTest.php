<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\LoginFactory;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\RefreshTokens;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\RefreshTokensFactory;
use Dvsa\Olcs\Api\Domain\Repository\User;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\MocksAbstractCommandHandlerServicesTrait;
use Dvsa\OlcsTest\MocksRepositoriesTrait;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Dvsa\OlcsTest\MocksServicesTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see RefreshTokensFactory
 */
class RefreshTokenFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;
    use MocksRepositoriesTrait;
    use MocksAbstractCommandHandlerServicesTrait;

    /**
     * @var LoginFactory
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->__invoke(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsAnInstanceOfRefreshTokenCommandHandler(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->__invoke($this->serviceManager, RefreshTokens::class);

        // Assert
        $this->assertInstanceOf(RefreshTokens::class, $result);
    }

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new RefreshTokensFactory();
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $this->authorizationService();
        $this->adapter();
        $this->userRepository();
        $this->setUpAbstractCommandHandlerServices();
    }

    /**
     * @return ValidatableAdapterInterface|m\MockInterface
     */
    protected function adapter(): m\MockInterface
    {
        if (! $this->serviceManager->has(ValidatableAdapterInterface::class)) {
            $this->serviceManager->setService(
                ValidatableAdapterInterface::class,
                $this->setUpMockService(ValidatableAdapterInterface::class)
            );
        }

        return $this->serviceManager->get(ValidatableAdapterInterface::class);
    }

    protected function authorizationService(): m\MockInterface
    {
        if (! $this->serviceManager->has(AuthorizationService::class)) {
            $this->serviceManager->setService(
                AuthorizationService::class,
                $this->setUpMockService(AuthorizationService::class)
            );
        }

        return $this->serviceManager->get(AuthorizationService::class);
    }

    /**
     * @return MockInterface|User
     */
    protected function userRepository(): mixed
    {
        $repositoryServiceManager = $this->repositoryServiceManager();
        if (! $repositoryServiceManager->has('User')) {
            $repositoryServiceManager->setService(
                'User',
                $this->setUpMockService(User::class)
            );
        }
        return $repositoryServiceManager->get('User');
    }
}
