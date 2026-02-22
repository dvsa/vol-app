<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process;

use Dvsa\Olcs\Api\Service\Publication\Process\PluginManager as ProcessPluginManager;
use Dvsa\Olcs\Api\Service\Publication\Process\ProcessInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers ProcessPluginManager
 */
class PluginManagerTest extends MockeryTestCase
{
    private ProcessPluginManager $sut;

    public function setUp(): void
    {
        $this->sut = new ProcessPluginManager($this->createStub(ContainerInterface::class));
    }

    public function testValidate(): void
    {
        $plugin = m::mock(ProcessInterface::class);

        $this->assertNull($this->sut->validate($plugin));
    }

    public function testValidateInvalid(): void
    {
        $this->expectException(InvalidServiceException::class);

        $this->sut->validate(null);
    }
}
