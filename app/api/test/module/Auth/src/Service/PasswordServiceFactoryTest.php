<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Service;

use Dvsa\Olcs\Auth\Service\PasswordService;
use Dvsa\Olcs\Auth\Service\PasswordServiceFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\OlcsTest\MocksServicesTrait;
use Mockery as m;

class PasswordServiceFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var PasswordServiceFactory
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
    public function invokeReturnsAnInstanceOfPasswordService()
    {
        // Setup
        $this->setUpSut();
        $this->setUpServiceManager();

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

        // Assert
        $this->assertInstanceOf(PasswordService::class, $result);
    }

    protected function setUpSut(): void
    {
        $this->sut = new PasswordServiceFactory();
    }
}
