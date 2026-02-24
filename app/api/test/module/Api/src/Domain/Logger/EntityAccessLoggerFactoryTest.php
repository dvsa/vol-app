<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Logger;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Logger\EntityAccessLogger;
use Dvsa\Olcs\Api\Domain\Logger\EntityAccessLoggerFactory;
use Dvsa\OlcsTest\MocksServicesTrait;
use Laminas\ServiceManager\ServiceManager;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

/**
 * @see EntityAccessLoggerFactory
 */
class EntityAccessLoggerFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var EntityAccessLoggerFactory
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsInstanceOfFactoryProduct(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $product = $this->sut->__invoke($this->serviceManager, EntityAccessLogger::class);

        // Assert
        $this->assertInstanceOf(EntityAccessLogger::class, $product);
    }

    protected function setUp(): void
    {
        $this->setUpServiceManager();
    }

    public function setUpSut(): void
    {
        $this->sut = new EntityAccessLoggerFactory();
    }

    public function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $this->authorizationService();
        $this->commandHandler();
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
        if (! $this->serviceManager->has('CommandHandlerManager')) {
            $instance = $this->setUpMockService(CommandHandlerManager::class);
            $this->serviceManager->setService('CommandHandlerManager', $instance);
        }
        return $this->serviceManager->get('CommandHandlerManager');
    }
}
