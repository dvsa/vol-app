<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererInterface;
use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererPluginManager
 */
class SectionRendererPluginManagerTest extends MockeryTestCase
{
    private SectionRendererPluginManager $sut;

    public function setUp(): void
    {
        $this->sut = new SectionRendererPluginManager($this->createMock(ContainerInterface::class));
    }

    public function testValidate(): void
    {
        $plugin = m::mock(SectionRendererInterface::class);

        $this->assertNull($this->sut->validate($plugin));
    }

    public function testValidateInvalid(): void
    {
        $this->expectException(InvalidServiceException::class);

        $this->sut->validate(null);
    }
}
