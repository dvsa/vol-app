<?php

namespace OlcsTest\Service\Marker;

use Interop\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Service\Marker\MarkerPluginManager;
use Olcs\Service\Marker\MarkerService;

class MarkerServiceTest extends TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new MarkerService();
        parent::setUp();
    }

    public function testInvoke()
    {
        $mockMarkerPlugin = m::mock(MarkerPluginManager::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->expects('get')->with(MarkerPluginManager::class)
            ->andReturn($mockMarkerPlugin);

        $obj = $this->sut->__invoke($mockSl, MarkerService::class);

        $this->assertInstanceOf(MarkerService::class, $obj);
        $this->assertSame($mockMarkerPlugin, $this->sut->getMarkerPluginManager());
    }

    public function testGetMarkers()
    {
        $data = ['DATA'];

        $this->sut->addData('KEY', $data);

        $mockMarkerPlugin = m::mock(MarkerPluginManager::class);
        $this->sut->setMarkerPluginManager($mockMarkerPlugin);

        $mockMarker1 = m::mock();
        $mockMarker2 = m::mock();

        $mockMarkerPlugin->shouldReceive('getMarkers')->with()->once()->andReturn(['mockMarker1', 'mockMarker2']);

        $mockMarkerPlugin->shouldReceive('get')->with('mockMarker1')->once()->andReturn($mockMarker1);
        $mockMarkerPlugin->shouldReceive('get')->with('mockMarker2')->once()->andReturn($mockMarker2);

        $mockMarker1->shouldReceive('setData')->with(['KEY' => $data])->once();
        $mockMarker1->shouldReceive('canRender')->with()->once()->andReturn(true);

        $mockMarker2->shouldReceive('setData')->with(['KEY' => $data])->once();
        $mockMarker2->shouldReceive('canRender')->with()->once()->andReturn(false);

        $markers = $this->sut->getMarkers();

        $this->assertCount(1, $markers);
        $this->assertSame([$mockMarker1], $markers);
    }
}
