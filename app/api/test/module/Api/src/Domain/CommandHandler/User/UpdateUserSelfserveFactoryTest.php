<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactioningCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\User\UpdateUserSelfserveFactory;
use Dvsa\Olcs\Api\Domain\Repository\User;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\MocksAbstractCommandHandlerServicesTrait;
use Dvsa\OlcsTest\MocksRepositoriesTrait;
use Dvsa\OlcsTest\MocksServicesTrait;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\ServiceManager;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\OlcsTest\Api\Domain\CommandHandler\User\UpdateUserSelfserveFactory::class)]
class UpdateUserSelfserveFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;
    use MocksRepositoriesTrait;
    use MocksAbstractCommandHandlerServicesTrait;

    /**
     * @var UpdateUserSelfserveFactory
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
    public function invokeReturnsAnInstanceOfTransactioningCommandHandler(): void
    {
        // Setup
        $this->setUpSut();

        // Expectations
        $repositoryServiceManager = $this->repositoryServiceManager();
        $repositoryServiceManager->expects('get')->with('User')->andReturn(m::mock(User::class));

        // Execute
        $result = $this->sut->__invoke($this->serviceManager, null);

        // Assert
        $this->assertInstanceOf(TransactioningCommandHandler::class, $result);
    }

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new UpdateUserSelfserveFactory();
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $this->setUpAbstractCommandHandlerServices();
        $this->authorisationService();
        $this->eventHistoryCreator();
        $this->passwordService();
        $this->adapter();
        $this->config();
        $this->repositoryServiceManager();
    }

    /**
     * @return AuthorizationService|m\MockInterface
     */
    protected function authorisationService(): m\MockInterface
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
     * @return EventHistoryCreator|m\MockInterface
     */
    protected function eventHistoryCreator(): m\MockInterface
    {
        if (! $this->serviceManager->has(EventHistoryCreator::class)) {
            $this->serviceManager->setService(
                EventHistoryCreator::class,
                $this->setUpMockService(EventHistoryCreator::class)
            );
        }

        return $this->serviceManager->get(EventHistoryCreator::class);
    }

    /**
     * @return PasswordService|m\MockInterface
     */
    protected function passwordService(): m\MockInterface
    {
        if (! $this->serviceManager->has(PasswordService::class)) {
            $this->serviceManager->setService(
                PasswordService::class,
                $this->setUpMockService(PasswordService::class)
            );
        }

        return $this->serviceManager->get(PasswordService::class);
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

    /**
     * @return array
     */
    protected function config(): array
    {
        if (! $this->serviceManager->has('config')) {
            $this->serviceManager->setService(
                'config',
                []
            );
        }

        return $this->serviceManager->get('config');
    }


    /**
     * @return RepositoryServiceManager|m\MockInterface
     */
    private function repositoryServiceManager(): m\MockInterface
    {
        if (!$this->serviceManager->has('RepositoryServiceManager')) {
            $instance = $this->setUpMockService(RepositoryServiceManager::class);
            $this->serviceManager->setService('RepositoryServiceManager', $instance);
        }

        return $this->serviceManager->get('RepositoryServiceManager');
    }
}
