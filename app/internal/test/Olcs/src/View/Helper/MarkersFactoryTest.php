<?php

namespace OlcsTest\View\Helper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class MarkersFactoryTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class MarkersFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $mockMarkersService = m::mock(\Olcs\Service\Marker\MarkerService::class);

        $mockSl = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $mockSl->shouldReceive('getServiceLocator->get')->with(\Olcs\Service\Marker\MarkerService::class)->once()
            ->andReturn($mockMarkersService);

        $sut = new \Olcs\View\Helper\MarkersFactory();

        $obj = $sut->createService($mockSl);

        $this->assertInstanceOf(\Olcs\View\Helper\RenderMarkers::class, $obj);
        $this->assertSame($mockMarkersService, $obj->getMarkerService());
    }
}
