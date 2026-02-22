<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Rbac;

use Dvsa\Olcs\Api\Rbac\IdentityProviderFactory;
use Dvsa\Olcs\Api\Rbac\JWTIdentityProvider;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\OlcsTest\MocksServicesTrait;

class IdentityProviderFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var IdentityProviderFactory
     */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->__invoke(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
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
}
