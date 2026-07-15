<?php

declare(strict_types=1);

namespace CommonTest\Common\Auth\Adapter;

use Common\Auth\Adapter\CommandAdapter;
use Common\Auth\Adapter\CommandAdapterFactory;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Test\MocksServicesTrait;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class CommandAdapterFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var CommandAdapterFactory
     */
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->setUpServiceManager();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable(fn(\Psr\Container\ContainerInterface $container, string $requestedName, ?array $options = null): \Common\Auth\Adapter\CommandAdapter => $this->sut->__invoke($container, $requestedName, $options));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsAnInstanceOfCommandAdapter(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertInstanceOf(CommandAdapter::class, $result);
    }

    protected function setUpSut(): void
    {
        $this->sut = new CommandAdapterFactory();
    }

    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $serviceManager->setService('CommandSender', m::mock(CommandSender::class));
        return $serviceManager;
    }
}
