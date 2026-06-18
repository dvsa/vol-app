<?php

declare(strict_types=1);

namespace CommonTest\Common\Rbac;

use Common\Auth\Service\RefreshTokenService;
use Common\Auth\SessionFactory;
use Common\Rbac\JWTIdentityProvider;
use Common\Rbac\JWTIdentityProviderFactory;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Test\MocksServicesTrait;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class JWTIdentityProviderFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    protected JWTIdentityProviderFactory $sut;

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
    public function invokeReturnsAnInstanceOfJWTIdentityProvider(): void
    {
        // Setup
        $this->setUpSut();
        $this->config(['auth' => ['session_name' => 'session']]);

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

        // Execute
        $this->sut->__invoke($this->serviceManager(), null);
    }

    protected function setUpSut(): void
    {
        $this->sut = new JWTIdentityProviderFactory();
    }

    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $serviceManager->setService('QuerySender', $this->setUpMockService(QuerySender::class));
        $serviceManager->setService(CacheEncryption::class, $this->setUpMockService(CacheEncryption::class));
        $this->config();
        $serviceManager->setService(RefreshTokenService::class, $this->setUpMockService(RefreshTokenService::class));
        $serviceManager->setService(Session::class, $this->setUpMockService(Session::class));

        return $serviceManager;
    }

    protected function config(array $config = []): void
    {
        $this->serviceManager->setService('config', $config);
    }
}
