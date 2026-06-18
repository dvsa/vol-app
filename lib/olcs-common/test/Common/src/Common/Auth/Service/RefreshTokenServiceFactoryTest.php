<?php

declare(strict_types=1);

namespace CommonTest\Common\Auth\Service;

use Common\Auth\Service\RefreshTokenService;
use Common\Auth\Service\RefreshTokenServiceFactory;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Test\MocksServicesTrait;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class RefreshTokenServiceFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    protected RefreshTokenServiceFactory $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->setUpServiceManager();
    }

    /**
     * @test
     */
    public function invokeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable(fn(\Psr\Container\ContainerInterface $container, string $requestedName, ?array $options = null): \Common\Auth\Service\RefreshTokenService => $this->sut->__invoke($container, $requestedName, $options));
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeReturnsAnInstanceOfRefreshTokenService(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertInstanceOf(RefreshTokenService::class, $result);
    }

    protected function setUpSut(): void
    {
        $this->sut = new RefreshTokenServiceFactory();
    }

    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $serviceManager->setService('CommandSender', $this->setUpMockService(CommandSender::class));
        return $serviceManager;
    }
}
