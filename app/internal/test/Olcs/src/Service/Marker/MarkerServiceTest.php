<?php

namespace OlcsTest\Service\Marker;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * MarkerServiceTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class MarkerServiceTest extends TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\MarkerService
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new \Olcs\Service\Marker\MarkerService();
        parent::setUp();
    }

    public function testCreateService()
    {
        $mockMarkerPlugin = m::mock(\Olcs\Service\Marker\MarkerPluginManager::class);

        $mockSl = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $mockSl->shouldReceive('get')->with(\Olcs\Service\Marker\MarkerPluginManager::class)->once()
            ->andReturn($mockMarkerPlugin);

        $obj = $this->sut->createService($mockSl);

        $this->assertInstanceOf(\Olcs\Service\Marker\MarkerService::class, $obj);
        $this->assertSame($mockMarkerPlugin, $this->sut->getMarkerPluginManager());
    }

    public function testGetMarkers()
    {
        $data = ['DATA'];

        $this->sut->addData('KEY', $data);

        $mockMarkerPlugin = m::mock(\Olcs\Service\Marker\MarkerPluginManager::class);
        $this->sut->setMarkerPluginManager($mockMarkerPlugin);

        $mockMarker1 = m::mock();
        $mockMarker2 = m::mock();

        $mockMarkerPlugin->shouldReceive('getRegisteredServices')->with()->once()->andReturn(
            [
                'invokableClasses' => ['mockMarker1', 'mockMarker2']
            ]
        );

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
