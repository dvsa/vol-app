<?php

namespace OlcsTest\View\Helper;

use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Service\Marker\MarkerService;
use Olcs\View\Helper\RenderMarkers;

/**
 * Class MarkersFactoryTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class MarkersFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $mockMarkersService = m::mock(MarkerService::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with(MarkerService::class)->once()
            ->andReturn($mockMarkersService);

        $sut = new \Olcs\View\Helper\MarkersFactory();

        $obj = $sut->__invoke($mockSl, RenderMarkers::class);

        $this->assertInstanceOf(RenderMarkers::class, $obj);
        $this->assertSame($mockMarkersService, $obj->getMarkerService());
    }
}
