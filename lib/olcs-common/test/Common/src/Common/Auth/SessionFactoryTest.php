<?php

declare(strict_types=1);

namespace CommonTest\Common\Auth;

use Common\Auth\SessionFactory;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Test\MocksServicesTrait;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class SessionFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var SessionFactory
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
        $this->assertIsCallable(fn(\Psr\Container\ContainerInterface $container, string $requestedName, ?array $options = null): \Laminas\Authentication\Storage\Session => $this->sut->__invoke($container, $requestedName, $options));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsAnInstanceOfSessionFactory(): void
    {
        // Setup
        $this->setUpSut();
        $this->config(['auth' => ['session_name' => 'session']]);

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertInstanceOf(Session::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeThrowsExceptionWhenConfigIsMissing(): void
    {
        // Setup
        $this->setUpSut();
        $this->config([]);

        // Expectations
        $this->expectException(\RuntimeException::class);

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    protected function setUpSut(): void
    {
        $this->sut = new SessionFactory();
    }

    protected function config(array $config = []): void
    {
        $this->serviceManager->setService('config', $config);
    }

    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $serviceManager->setService('CommandSender', m::mock(CommandSender::class));
        return $serviceManager;
    }
}
