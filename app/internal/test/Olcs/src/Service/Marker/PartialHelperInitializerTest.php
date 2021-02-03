<?php

/**
 * Partial Helper Initializer Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
namespace OlcsTest\Service\Marker;

use Olcs\Service\Marker\PartialHelperInitializer;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager;
use Laminas\View\Helper\Partial;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;

/**
 * Partial Helper Initializer Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
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

        $parentServiceLocator = m::mock(ServiceLocatorInterface::class);
        $parentServiceLocator->shouldReceive('get')
            ->with('ViewHelperManager')
            ->andReturn($helperPluginManager);

        $this->serviceLocator = m::mock(ServiceLocatorInterface::class);
        $this->serviceLocator->shouldReceive('getServiceLocator')
            ->withNoArgs()
            ->andReturn($parentServiceLocator);

        $this->instance = m::mock(stdClass::class);
        $this->instance->shouldReceive('setPartialHelper')
            ->with($partial)
            ->once();

        $this->sut = new PartialHelperInitializer();
    }

    public function testInitialize()
    {
        $this->assertSame(
            $this->instance,
            $this->sut->initialize($this->instance, $this->serviceLocator)
        );
    }

    public function testInvoke()
    {
        $this->assertSame(
            $this->instance,
            ($this->sut)($this->serviceLocator, $this->instance)
        );
    }
}
