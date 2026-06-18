<?php

namespace CommonTest\Form\Element;

use Common\Form\Element\DynamicSelect;
use Common\Form\Element\DynamicSelectFactory;
use Common\Service\Data\PluginManager;
use Psr\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class DynamicSelectFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $pluginManager = m::mock(PluginManager::class);
        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->expects('get')->with('DataServiceManager')->andReturn($pluginManager);

        $sut = new DynamicSelectFactory();
        $service = $sut->__invoke($mockSl, DynamicSelect::class);

        $this->assertInstanceOf(DynamicSelect::class, $service);
    }
}
