<?php

namespace OlcsTest\Listener\RouteParam;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Psr\Container\ContainerInterface;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManagerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\TransportManagerMarker;
use Olcs\Listener\RouteParams;
use Olcs\Service\Marker\MarkerService;
use Laminas\ServiceManager\ServiceLocatorInterface;

class TransportManagerMarkerTest extends MockeryTestCase
{
    /** @var  TransportManagerMarker */
    protected $sut;
    /** @var  m\MockInterface */
    private $mockQueryService;
    /** @var  m\MockInterface */
    private $mockAnnotationBuilderService;

    /** @var m\MockInterface|MarkerService  */
    private $mockMarkerService;

    public function setUp(): void
    {
        $this->sut = new TransportManagerMarker();

        $this->mockQueryService = m::mock();
        $this->mockAnnotationBuilderService = m::mock();

        $this->sut->setAnnotationBuilderService($this->mockAnnotationBuilderService);
        $this->sut->setQueryService($this->mockQueryService);

        /** @var MarkerService $mockMarkerService */
        $this->mockMarkerService = m::mock(MarkerService::class);
        $this->sut->setMarkerService($this->mockMarkerService);
    }

    /**
     * Test attach
     *
     * @group transportManagerMarker
     */
    public function testAttach()
    {
        /** @var EventManagerInterface $mockEventManager */
        $mockEventManager = m::mock(EventManagerInterface::class)
            ->shouldReceive('attach')
            ->with(RouteParams::EVENT_PARAM . 'transportManager', [$this->sut, 'onTransportManagerMarker'], 1)
            ->once()
            ->shouldReceive('attach')
            ->with(RouteParams::EVENT_PARAM . 'licence', [$this->sut, 'onLicenceTransportManagerMarker'], 1)
            ->once()
            ->shouldReceive('attach')
            ->with(RouteParams::EVENT_PARAM . 'application', [$this->sut, 'onApplicationTransportManagerMarker'], 1)
            ->once()
            ->getMock();

        $this->sut->attach($mockEventManager);
    }

    /**
     * Test create service
     *
     * @group transportManagerMarker
     */
    public function testInvoke()
    {
        $mockSl = m::mock(ContainerInterface::class);

        $mockMarkerService = m::mock(MarkerService::class);
        $mockQueryService = m::mock();
        $mockAnnotationBuilderService = m::mock();
        $mockApplicationService = m::mock();

        $mockSl->shouldReceive('get')->with(MarkerService::class)->once()
            ->andReturn($mockMarkerService);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->once()
            ->andReturn($mockAnnotationBuilderService);
        $mockSl->shouldReceive('get')->with('QueryService')->once()->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with('Application')->once()->andReturn($mockApplicationService);

        $obj = $this->sut->__invoke($mockSl, TransportManagerMarker::class);

        $this->assertSame($mockAnnotationBuilderService, $obj->getAnnotationBuilderService());
        $this->assertSame($mockMarkerService, $obj->getMarkerService());
        $this->assertSame($mockQueryService, $obj->getQueryService());
        $this->assertSame($mockApplicationService, $obj->getApplicationService());

        $this->assertInstanceOf(TransportManagerMarker::class, $obj);
    }

    protected function mockQuery($expectedDtoParams, $result = false, $extra = null)
    {
        $mockResponse = m::mock();

        $this->mockAnnotationBuilderService
            ->shouldReceive('createQuery')
            ->once()
            ->andReturnUsing(
                function (AbstractQuery $dto) use ($expectedDtoParams) {
                    $this->assertSame($expectedDtoParams, $dto->getArrayCopy());
                    return 'QUERY';
                }
            );

        $this->mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResponse);
        if ($result === false) {
            $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(false);
        } else {
            $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
            $return = ['result' => $result, 'results' => $result];
            if ($extra !== null) {
                $return['extra'] = $extra;
            }
            $mockResponse->shouldReceive('getResult')->with()->once()->andReturn($return);
        }
    }

    /**
     * Test on transport manager marker
     *
     * @group transportManagerMarker
     */
    public function testOnTransportManagerMarker()
    {
        $this->mockQuery(['id' => 12], 'TM');
        $this->mockMarkerService->shouldReceive('addData')
            ->with('transportManager', ['result'=>'TM', 'results'=>'TM'])->once();

        $this->mockQuery(
            [
                'user' => null,
                'application' => null,
                'transportManager' => 12,
                'appStatuses' => [],
                'filterByOrgUser' => null,
            ],
            'TMAs'
        );
        $this->mockMarkerService->shouldReceive('addData')->with('transportManagerApplications', 'TMAs')->once();

        $this->mockQuery(['licence' => null, 'transportManager' => 12], 'TMLs');
        $this->mockMarkerService->shouldReceive('addData')->with('transportManagerLicences', 'TMLs')->once();

        $this->mockMarkerService->shouldReceive('addData')->with('page', 'transportManager')->once();

        $routeParam = new RouteParam();
        $routeParam->setValue(12);

        $event = new Event(null, $routeParam);

        $this->sut->onTransportManagerMarker($event);
    }

    /**
     * Test on licence transport manager marker
     *
     * @group transportManagerMarker
     */
    public function testOnLicenceTransportManagerMarker()
    {
        $this->mockQuery(['licence' => 18, 'transportManager' => null], 'TMLs');
        $this->mockMarkerService->shouldReceive('addData')->with('transportManagerLicences', 'TMLs')->once();
        $this->mockMarkerService->shouldReceive('addData')->with('page', 'transportManagerLicence')->once();

        $routeParam = new RouteParam();
        $routeParam->setValue(18);

        $event = new Event(null, $routeParam);

        $this->sut->onLicenceTransportManagerMarker($event);
    }

    public function testOnLicenceTransportManagerMarkerQueryError()
    {
        $this->mockQuery(['licence' => 18, 'transportManager' => null], false);

        $routeParam = new RouteParam();
        $routeParam->setValue(18);

        $event = new Event(null, $routeParam);

        $this->expectException(\RuntimeException::class);

        $this->sut->onLicenceTransportManagerMarker($event);
    }

    /**
     * Test on application transport manager marker
     *
     * @group transportManagerMarker
     */
    public function testOnApplicationTransportManagerMarker()
    {
        $mockApplicationService = m::mock()
            ->shouldReceive('getMvcEvent')
            ->andReturn(
                m::mock()
                ->shouldReceive('getRouteMatch')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getMatchedRouteName')
                    ->andReturn('lva-application')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->sut->setApplicationService($mockApplicationService);

        $this->mockQuery(
            [
                'user' => null,
                'application' => 534,
                'transportManager' => null,
                'appStatuses' => [],
                'filterByOrgUser' => null,
            ],
            'TMAs'
        );
        $this->mockMarkerService->shouldReceive('addData')->with('transportManagerApplications', 'TMAs')->once();
        $this->mockMarkerService->shouldReceive('addData')->with('page', 'transportManagerApplication')->once();

        $routeParam = new RouteParam();
        $routeParam->setValue(534);

        $event = new Event(null, $routeParam);

        $this->sut->onApplicationTransportManagerMarker($event);
    }

    /**
     * Test on application transport manager marker
     *
     * @group transportManagerMarker
     */
    public function testOnApplicationTransportManagerMarkerWithVariationRoute()
    {
        $mockApplicationService = m::mock()
            ->shouldReceive('getMvcEvent')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getRouteMatch')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getMatchedRouteName')
                            ->andReturn('lva-variation')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->sut->setApplicationService($mockApplicationService);

        $this->mockQuery(
            [
                'user' => null,
                'application' => 534,
                'transportManager' => null,
                'appStatuses' => [],
                'filterByOrgUser' => null,
            ],
            'TMAs'
        );
        $this->mockMarkerService->shouldReceive('addData')->with('transportManagerApplications', 'TMAs')->once();

        $this->mockQuery(['variation' => 534], 'TMAs1', ['requiresSiQualification' => true]);
        $this->mockMarkerService->shouldReceive('addData')->with('transportManagersFromLicence', 'TMAs1')->once();

        $this->mockMarkerService->shouldReceive('addData')->with('page', 'transportManagerVariation')->once();

        $routeParam = new RouteParam();
        $routeParam->setValue(534);

        $event = new Event(null, $routeParam);

        $this->sut->onApplicationTransportManagerMarker($event);
    }

    public function testOnApplicationTransportManagerMarkerWithVariationRouteNoSiRequired()
    {
        $mockApplicationService = m::mock()
            ->shouldReceive('getMvcEvent')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getRouteMatch')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getMatchedRouteName')
                            ->andReturn('lva-variation')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->sut->setApplicationService($mockApplicationService);

        $this->mockQuery(
            [
                'user' => null,
                'application' => 534,
                'transportManager' => null,
                'appStatuses' => [],
                'filterByOrgUser' => null,
            ],
            'TMAs'
        );
        $this->mockMarkerService->shouldReceive('addData')->with('transportManagerApplications', 'TMAs')->once();

        $this->mockQuery(['variation' => 534], 'TMAs1', ['requiresSiQualification' => false]);

        $this->mockMarkerService->shouldReceive('addData')->with('page', 'transportManagerVariation')->once();

        $routeParam = new RouteParam();
        $routeParam->setValue(534);

        $event = new Event(null, $routeParam);

        $this->sut->onApplicationTransportManagerMarker($event);
    }

    /**
     * @group transportManagerMarker
     */
    public function testOnApplicationTransportManagerMarkerQueryError()
    {
        $this->mockQuery(
            [
                'user' => null,
                'application' => 534,
                'transportManager' => null,
                'appStatuses' => [],
                'filterByOrgUser' => null,
            ],
            false
        );

        $routeParam = new RouteParam();
        $routeParam->setValue(534);

        $event = new Event(null, $routeParam);

        $this->expectException(\RuntimeException::class);

        $this->sut->onApplicationTransportManagerMarker($event);
    }

    /**
     * @group transportManagerMarker
     */
    public function testOnApplicationTransportManagerMarkerWithVariationRouteQueryError()
    {
        $mockApplicationService = m::mock()
            ->shouldReceive('getMvcEvent')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getRouteMatch')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getMatchedRouteName')
                            ->andReturn('lva-variation')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->sut->setApplicationService($mockApplicationService);

        $this->mockQuery(
            [
                'user' => null,
                'application' => 534,
                'transportManager' => null,
                'appStatuses' => [],
                'filterByOrgUser' => null,
            ],
            'TMAs'
        );
        $this->mockMarkerService->shouldReceive('addData')->with('transportManagerApplications', 'TMAs')->once();

        $this->mockQuery(['variation' => 534], false);

        $routeParam = new RouteParam();
        $routeParam->setValue(534);

        $event = new Event(null, $routeParam);

        $this->expectException(\RuntimeException::class);

        $this->sut->onApplicationTransportManagerMarker($event);
    }
}
