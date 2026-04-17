<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\CommandHandler\User\CreateUserSelfServeFactory;
use Dvsa\Olcs\Api\Domain\Repository\User;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\MocksAbstractCommandHandlerServicesTrait;
use Dvsa\OlcsTest\MocksServicesTrait;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\ServiceManager;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\CommandHandler\User\CreateUserSelfserve;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class CreateUserSelfserveFactoryTest
 * @see CreateUserSelfserveFactory
 */
class CreateUserSelfserveFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;
    use MocksAbstractCommandHandlerServicesTrait;

    /**
     * @var CreateUserSelfserveFactory
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
    public function invokeReturnsWrappedCreateUserSelfserveCommandHandler(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->__invoke($this->serviceManager, CreateUserSelfserve::class);

        // Assert
        $this->assertInstanceOf(CreateUserSelfserve::class, $result->getWrapped());
    }

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new CreateUserSelfserveFactory();
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $this->setUpAbstractCommandHandlerServices();
        $serviceManager->setService(AuthorizationService::class, $this->setUpMockService(AuthorizationService::class));
        $serviceManager->setService(ValidatableAdapterInterface::class, $this->setUpMockService(ValidatableAdapterInterface::class));
        $serviceManager->setService(PasswordService::class, $this->setUpMockService(PasswordService::class));
        $this->setupRespositories();
    }

    private function setupRespositories(): void
    {
        $repositoryServiceManager = $this->serviceManager->get('RepositoryServiceManager');
        assert($repositoryServiceManager instanceof RepositoryServiceManager);
        $mockUserRepository = m::mock(User::class);
        $repositoryServiceManager->setService('User', $mockUserRepository);
    }
}
