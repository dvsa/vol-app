<?php

declare(strict_types=1);

namespace CommonTest\Common\Rbac;

use Common\Rbac\IdentityProviderFactory;
use Common\Rbac\JWTIdentityProvider;
use Common\Rbac\JWTIdentityProviderFactory;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Test\MocksServicesTrait;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class IdentityProviderFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var JWTIdentityProviderFactory
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
        $this->assertIsCallable(fn(\Psr\Container\ContainerInterface $container, string $requestedName, ?array $options = null): \Common\Rbac\JWTIdentityProvider => $this->sut->__invoke($container, $requestedName, $options));
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeReturnsInstanceWhenItImplementsIdentityProviderInterface(): void
    {
        // Setup
        $this->setUpSut();
        $this->config(['auth' => ['identity_provider' => JWTIdentityProvider::class]]);
        $this->serviceManager->setService(JWTIdentityProvider::class, $this->setUpMockService(JWTIdentityProvider::class));

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertInstanceOf(JWTIdentityProvider::class, $result);
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeThrowsExceptionWhenConfigIsMissing(): void
    {
        // Setup
        $this->setUpSut();
        $this->config([]);

        // Expectations
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(IdentityProviderFactory::MESSAGE_CONFIG_MISSING);

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeThrowsExceptionWhenContainerDoesNotHaveRequestedInstance(): void
    {
        // Setup
        $this->setUpSut();
        $this->config(['auth' => ['identity_provider' => JWTIdentityProvider::class]]);

        // Expectations
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(IdentityProviderFactory::MESSAGE_UNABLE_TO_CREATE);

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeThrowsExceptionWhenInstanceDoesNotImplementIdentityProviderInterface(): void
    {
        // Setup
        $this->setUpSut();
        $this->config(['auth' => ['identity_provider' => static::class]]);
        $this->serviceManager->setService(static::class, $this->setUpMockService(static::class));

        // Expectations
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(IdentityProviderFactory::MESSAGE_DOES_NOT_IMPLEMENT);

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    protected function setUpSut(): void
    {
        $this->sut = new IdentityProviderFactory();
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
