<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\ChangePassword;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\ChangePasswordFactory;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\MocksAbstractCommandHandlerServicesTrait;
use Dvsa\OlcsTest\Api\Domain\Repository\MocksUserRepositoryTrait;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\OlcsTest\MocksServicesTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see ChangePasswordFactory
 */
class ChangePasswordFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;
    use MocksAbstractCommandHandlerServicesTrait;
    use MocksUserRepositoryTrait;

    protected ChangePasswordFactory $sut;

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new ChangePasswordFactory();
    }

    public function testServiceCreated(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->__invoke($this->serviceManager, ChangePassword::class);

        // Assert
        $this->assertInstanceOf(ChangePassword::class, $result);
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $this->setUpAbstractCommandHandlerServices();
        $this->getMockService(ValidatableAdapterInterface::class);
        $this->getMockService(AuthorizationService::class);
        $this->userRepository();
    }
}
