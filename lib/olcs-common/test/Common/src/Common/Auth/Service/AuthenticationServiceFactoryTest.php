<?php

declare(strict_types=1);

namespace CommonTest\Common\Auth\Service;

use Common\Auth\Service\AuthenticationService;
use Common\Auth\Service\AuthenticationServiceFactory;
use Common\Test\MocksServicesTrait;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\ServiceManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AuthenticationServiceFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var AuthenticationServiceFactory
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
        $this->assertIsCallable(fn(\Psr\Container\ContainerInterface $container, string $requestedName, ?array $options = null): \Common\Auth\Service\AuthenticationService => $this->sut->__invoke($container, $requestedName, $options));
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeReturnsAnInstanceOfAuthenticationService(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertInstanceOf(AuthenticationService::class, $result);
    }

    protected function setUpSut(): void
    {
        $this->sut = new AuthenticationServiceFactory();
    }

    /**
     * @return void
     */
    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $serviceManager->setService(Session::class, $this->setUpMockService(Session::class));
        return $serviceManager;
    }
}
