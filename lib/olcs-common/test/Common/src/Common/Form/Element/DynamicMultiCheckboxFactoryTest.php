<?php

declare(strict_types=1);

namespace CommonTest\Form\Element;

use Common\Form\Element\DynamicMultiCheckbox;
use Common\Form\Element\DynamicMultiCheckboxFactory;
use Common\Service\Data\PluginManager;
use Psr\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class DynamicMultiCheckboxFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $pluginManager = m::mock(PluginManager::class);
        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->expects('get')->with('DataServiceManager')->andReturn($pluginManager);

        $sut = new DynamicMultiCheckboxFactory();
        $service = $sut->__invoke($mockSl, DynamicMultiCheckbox::class);

        $this->assertInstanceOf(DynamicMultiCheckbox::class, $service);
    }
}
