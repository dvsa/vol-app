<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Service;

use Dvsa\Olcs\Auth\Service\AuthenticationService;
use Dvsa\Olcs\Auth\Service\AuthenticationServiceFactory;
use Dvsa\OlcsTest\MocksServicesTrait;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class AuthenticationServiceFactoryTest
 * @see AuthenticationServiceFactory
 */
class AuthenticationServiceFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var AuthenticationServiceFactory
     */
    protected $sut;

    /**
     * @test
     */
    public function invokeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->__invoke(...));
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeReturnsAnInstanceOfAuthenticationService()
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertInstanceOf(AuthenticationService::class, $result);
    }

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new AuthenticationServiceFactory();
    }
}
