<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\ResetPassword;
use Dvsa\Olcs\Api\Domain\CommandHandler\Auth\ResetPasswordFactory;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\MocksAbstractCommandHandlerServicesTrait;
use Dvsa\OlcsTest\Api\Domain\Repository\MocksUserRepositoryTrait;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\OlcsTest\MocksServicesTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see ResetPasswordFactory
 */
class ResetPasswordFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;
    use MocksAbstractCommandHandlerServicesTrait;
    use MocksUserRepositoryTrait;

    protected ResetPasswordFactory $sut;

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new ResetPasswordFactory();
    }

    public function testServiceCreated(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->__invoke($this->serviceManager, ResetPassword::class);

        // Assert
        $this->assertInstanceOf(ResetPassword::class, $result);
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $this->setUpAbstractCommandHandlerServices();
        $this->getMockService(ValidatableAdapterInterface::class);
        $this->getMockService(AuthorizationService::class);
        $this->getMockServiceWithName(EventHistoryCreator::class, 'EventHistoryCreator');
        $this->userRepository();
    }
}
