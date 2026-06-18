<?php

namespace CommonTest\Common\Service\Cqrs\Command;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Command\CommandServiceFactory;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Test\MocksServicesTrait;
use Laminas\Http\Client\Adapter\Curl;
use Laminas\Http\Request;
use Laminas\Router\RouteInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

class CommandServiceFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var CommandServiceFactory
     */
    protected $sut;

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
        $this->assertIsCallable(fn(\Psr\Container\ContainerInterface $container, string $requestedName, ?array $options = null): \Common\Service\Cqrs\Command\CommandService => $this->sut->__invoke($container, $requestedName, $options));
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeReturnsAnInstanceOfCommandService(): void
    {
        // Setup
        $this->setUpSut();
        $this->config([
            'cqrs_client' => [
                'adapter' => m::mock(Curl::class)->makePartial()
            ],
            'debug' => [
                'showApiMessages' => true
            ],
            'auth' => [
                'session_name' => 'session'
            ]
        ]);

        // Execute
        $commandService = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertInstanceOf(CommandService::class, $commandService);
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeThrowsExceptionWhenConfigMissing(): void
    {
        // Setup
        $this->setUpSut();

        // Expectations
        $this->expectException(RuntimeException::class);

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    protected function setUpSut(): void
    {
        $this->sut = new CommandServiceFactory();
    }

    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $this->config();
        $serviceManager->setService('CqrsRequest', $this->setUpMockService(Request::class));
        $serviceManager->setService('ApiRouter', $this->setUpMockService(RouteInterface::class));
        $serviceManager->setService('Helper\FlashMessenger', $this->setUpMockService(FlashMessengerHelperService::class));
        return $serviceManager;
    }

    protected function config(array $config = []): void
    {
        $this->serviceManager->setService('Config', $config);
    }
}
