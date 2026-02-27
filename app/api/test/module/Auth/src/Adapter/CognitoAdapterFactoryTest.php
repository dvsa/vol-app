<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Adapter;

use Dvsa\Authentication\Cognito\Client;
use Dvsa\Olcs\Auth\Adapter\CognitoAdapter;
use Dvsa\Olcs\Auth\Adapter\CognitoAdapterFactory;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\OlcsTest\MocksServicesTrait;

class CognitoAdapterFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var CognitoAdapterFactory
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
    public function invokeReturnsAnInstanceOfCognitoAdapter(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertInstanceOf(CognitoAdapter::class, $result);
    }

    protected function setUpSut(): void
    {
        $this->sut = new CognitoAdapterFactory();
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $serviceManager->setService(Client::class, m::mock(Client::class));
    }
}
