<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererPluginManager;
use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererPluginManagerFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererPluginManagerFactory::class)]
class SectionRendererPluginManagerFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockContainer = m::mock(ContainerInterface::class);
        $mockContainer->shouldReceive('get')
            ->with('config')
            ->andReturn([])
            ->getMock();

        $factory = new SectionRendererPluginManagerFactory();
        $result = $factory->__invoke($mockContainer, SectionRendererPluginManager::class);

        $this->assertInstanceOf(SectionRendererPluginManager::class, $result);
    }
}
