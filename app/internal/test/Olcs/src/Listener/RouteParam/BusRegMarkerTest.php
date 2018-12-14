<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\BusRegMarker;
use Mockery as m;
use Olcs\Listener\RouteParams;

/**
 * Class BusRegMarkerTest
 * @package OlcsTest\Listener\RouteParam
 */
class BusRegMarkerTest extends TestCase
{
    public function setUp()
    {
        $this->sut = new BusRegMarker();
        parent::setUp();
    }

    public function testAttach()
    {
        $sut = new BusRegMarker();

        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'busRegId', [$sut, 'onBusRegMarker'], 1);

        $sut->attach($mockEventManager);
    }

    protected function setupBusRegMarker($id, $busRegData)
    {
        $mockAnnotationBuilder = m::mock();
        $mockQueryService  = m::mock();

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($id) {
                $this->assertSame($id, $dto->getId());
                return 'QUERY';
            }
        );

        $mockResult = m::mock();
        if ($busRegData === false) {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);
        } else {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
            $mockResult->shouldReceive('getResult')->with()->once()->andReturn($busRegData);
        }

        $mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResult);

        $this->sut->setAnnotationBuilderService($mockAnnotationBuilder);
        $this->sut->setQueryService($mockQueryService);
    }

    public function testOnBusRegMarker()
    {
        $busRegId = 1;
        $busReg = ['id' => $busRegId];

        $mockMarkerService = m::mock(\Olcs\Service\Marker\MarkerService::class);
        $this->sut->setMarkerService($mockMarkerService);

        $this->setupBusRegMarker($busRegId, $busReg);

        $mockMarkerService->shouldReceive('addData')->with('busReg', $busReg);

        $event = new RouteParam();
        $event->setValue($busRegId);

        $this->sut->onBusRegMarker($event);
    }

    public function testOnBusRegMarkerQueryError()
    {
        $busRegId = 1;

        $this->setupBusRegMarker($busRegId, false);

        $event = new RouteParam();
        $event->setValue($busRegId);

        $this->expectException(\RuntimeException::class);

        $this->sut->onBusRegMarker($event);
    }

    public function testCreateService()
    {
        $mockTransferAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();
        $mockMarkerService = m::mock(\Olcs\Service\Marker\MarkerService::class);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockTransferAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with(\Olcs\Service\Marker\MarkerService::class)->andReturn($mockMarkerService);

        $service = $this->sut->createService($mockSl);

        $this->assertSame($this->sut, $service);
        $this->assertSame($mockTransferAnnotationBuilder, $this->sut->getAnnotationBuilderService());
        $this->assertSame($mockQueryService, $this->sut->getQueryService());
        $this->assertSame($mockMarkerService, $this->sut->getMarkerService());
    }
}
