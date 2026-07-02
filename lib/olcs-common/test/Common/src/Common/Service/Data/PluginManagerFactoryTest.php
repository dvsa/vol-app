<?php

namespace CommonTest\Common\Service\Data;

use Common\Service\Data\PluginManager;
use Psr\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Data\PluginManagerFactory;
use Mockery as m;

class PluginManagerFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $config = [
            'data_services' => [
                'services' => [
                    'test' => 'dataService'
                ]
            ]
        ];

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn($config);
        $mockSl->shouldIgnoreMissing();

        $sut = new PluginManagerFactory();

        $service = $sut->__invoke($mockSl, PluginManager::class);

        $this->assertInstanceOf(PluginManager::class, $service);
        $this->assertEquals('dataService', $service->get('test'));
    }
}
