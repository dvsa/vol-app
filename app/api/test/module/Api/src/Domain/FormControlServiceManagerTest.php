<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain;

use Dvsa\Olcs\Api\Domain\FormControlServiceManager;
use Dvsa\Olcs\Api\Service\Qa\Strategy\FormControlStrategyInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class FormControlServiceManagerTest extends MockeryTestCase
{
    private FormControlServiceManager $sut;

    public function setUp(): void
    {
        $container = m::mock(ContainerInterface::class);
        $this->sut = new FormControlServiceManager($container, []);
    }

    public function testValidate(): void
    {
        $this->assertNull($this->sut->validate(m::mock(FormControlStrategyInterface::class)));
    }

    public function testValidateInvalid(): void
    {
        $this->expectException(InvalidServiceException::class);
        $this->sut->validate(null);
    }
}
