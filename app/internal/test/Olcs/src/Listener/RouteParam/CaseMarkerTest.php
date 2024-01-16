<?php

namespace OlcsTest\Listener\RouteParam;

use Interop\Container\ContainerInterface;
use Laminas\EventManager\Event;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\CaseMarker;
use Mockery as m;
use Olcs\Listener\RouteParams;
use Olcs\Service\Marker\MarkerService;

class CaseMarkerTest extends TestCase
{
    public function setUp(): void
    {
        $this->sut = new CaseMarker();

        $this->mockQueryService = m::mock();
        $this->mockAnnotationBuilderService = m::mock();

        $this->sut->setAnnotationBuilderService($this->mockAnnotationBuilderService);
        $this->sut->setQueryService($this->mockQueryService);
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Laminas\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'case', [$this->sut, 'onCase'], 1);

        $this->sut->attach($mockEventManager);
    }

    protected function mockQuery($expectedDtoParams, $result = false)
    {
        $mockResponse = m::mock();

        $this->mockAnnotationBuilderService->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($expectedDtoParams) {
                $this->assertSame($expectedDtoParams, $dto->getArrayCopy());
                return 'QUERY';
            }
        );

        $this->mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResponse);
        if ($result === false) {
            $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(false);
        } else {
            $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
            $mockResponse->shouldReceive('getResult')->with()->once()
                ->andReturn($result);
        }
    }

    public function testOnCase()
    {
        $mockMarkerService = m::mock(MarkerService::class);
        $this->sut->setMarkerService($mockMarkerService);

        $case = [
            'licence' => [
                'organisation' => 'ORG'
            ]
        ];

        $this->mockQuery(['id' => 128,], $case);
        $mockMarkerService->shouldReceive('addData')->with('organisation', 'ORG')->once();
        $mockMarkerService->shouldReceive('addData')->with('cases', [$case])->once();
        $mockMarkerService->shouldReceive('addData')->with('configCase', ['hideLink' => true])->once();

        $routeParam = new RouteParam();
        $routeParam->setValue(128);

        $event = new Event(null, $routeParam);

        $this->sut->onCase($event);
    }

    public function testOnCaseQueryError()
    {
        $mockMarkerService = m::mock(MarkerService::class);
        $this->sut->setMarkerService($mockMarkerService);

        $this->mockQuery(['id' => 128,], false);

        $routeParam = new RouteParam();
        $routeParam->setValue(128);

        $event = new Event(null, $routeParam);

        $this->expectException(\RuntimeException::class);

        $this->sut->onCase($event);
    }

    public function testInvoke()
    {
        $mockSl = m::mock(ContainerInterface::class);
        $mockMarkerService = m::mock(MarkerService::class);
        $mockQueryService = m::mock();
        $mockAnnotationBuilderService = m::mock();

        $mockSl->shouldReceive('get')->with(MarkerService::class)->once()
            ->andReturn($mockMarkerService);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->once()
            ->andReturn($mockAnnotationBuilderService);
        $mockSl->shouldReceive('get')->with('QueryService')->once()->andReturn($mockQueryService);

        $obj = $this->sut->__invoke($mockSl, CaseMarker::class);

        $this->assertSame($mockAnnotationBuilderService, $obj->getAnnotationBuilderService());
        $this->assertSame($mockMarkerService, $obj->getMarkerService());
        $this->assertSame($mockQueryService, $obj->getQueryService());

        $this->assertInstanceOf(CaseMarker::class, $obj);
    }
}
