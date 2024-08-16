<?php

namespace OlcsTest\Service\Marker;

use Psr\Container\ContainerInterface;
use Olcs\Service\Marker\PartialHelperInitializer;
use Laminas\View\HelperPluginManager;
use Laminas\View\Helper\Partial;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;

class PartialHelperInitializerTest extends MockeryTestCase
{
    private $serviceLocator;

    private $instance;

    private $sut;

    public function setUp(): void
    {
        $partial = m::mock(Partial::class);

        $helperPluginManager = m::mock(HelperPluginManager::class);
        $helperPluginManager->shouldReceive('get')
            ->with('partial')
            ->andReturn($partial);

        $parentServiceLocator = m::mock(ContainerInterface::class);
        $parentServiceLocator->shouldReceive('get')
            ->with('ViewHelperManager')
            ->andReturn($helperPluginManager);

        $this->serviceLocator = $parentServiceLocator;

        $this->instance = m::mock(stdClass::class);
        $this->instance->shouldReceive('setPartialHelper')
            ->with($partial)
            ->once();

        $this->sut = new PartialHelperInitializer();
    }

    public function testInvoke()
    {
        $this->assertSame(
            $this->instance,
            ($this->sut)($this->serviceLocator, $this->instance)
        );
    }
}
