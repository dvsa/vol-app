<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\ChangeExpiredPassword;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\ChangeExpiredPasswordFactory;
use Dvsa\Olcs\Api\Domain\Repository\User;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\MocksAbstractCommandHandlerServicesTrait;
use Dvsa\OlcsTest\MocksRepositoriesTrait;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\OlcsTest\MocksServicesTrait;

/**
 * @see ChangeExpiredPasswordFactory
 */
class ChangeExpiredPasswordFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;
    use MocksRepositoriesTrait;
    use MocksAbstractCommandHandlerServicesTrait;

    /**
     * @var ChangeExpiredPasswordFactory
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
    public function invokeReturnsAnInstanceOfChangeExpiredPasswordCommandHandler(): void
    {
        // Setup
        $this->setUpSut();

        // Expectations
        $repositoryServiceManager = $this->repositoryServiceManager();
        $repositoryServiceManager->expects('get')->with('User')->andReturn(m::mock(User::class));

        // Execute
        $result = $this->sut->__invoke($this->serviceManager, ChangeExpiredPassword::class);

        // Assert
        $this->assertInstanceOf(ChangeExpiredPassword::class, $result);
    }

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new ChangeExpiredPasswordFactory();
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $this->adapter();
        $this->repositoryServiceManager();
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

    private function repositoryServiceManager(): mixed
    {
        if (!$this->serviceManager->has('RepositoryServiceManager')) {
            $instance = $this->setUpMockService(RepositoryServiceManager::class);
            $this->serviceManager->setService('RepositoryServiceManager', $instance);
        }
        $instance = $this->serviceManager->get('RepositoryServiceManager');

        return $instance;
    }
}
