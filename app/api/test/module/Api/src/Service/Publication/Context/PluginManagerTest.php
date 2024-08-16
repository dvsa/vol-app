<?php

namespace Dvsa\OlcsTest\Api\Service\Publication\Context;

use Dvsa\Olcs\Api\Service\Publication\Context\ContextInterface;
use Dvsa\Olcs\Api\Service\Publication\Context\PluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Dvsa\Olcs\Api\Service\Publication\Context\PluginManager
 */
class PluginManagerTest extends MockeryTestCase
{
    private PluginManager $sut;

    public function setUp(): void
    {
        $this->sut = new PluginManager($this->createMock(ContainerInterface::class));
    }

    public function testValidate()
    {
        $plugin = m::mock(ContextInterface::class);

        $this->assertNull($this->sut->validate($plugin));
    }

    public function testValidateInvalid()
    {
        $this->expectException(InvalidServiceException::class);

        $this->sut->validate(null);
    }
}
