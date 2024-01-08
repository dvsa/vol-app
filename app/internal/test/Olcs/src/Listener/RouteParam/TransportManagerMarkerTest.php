<?php

namespace OlcsTest\Listener\RouteParam;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\TransportManagerMarker;
use Olcs\Listener\RouteParams;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Transport Manager Markers Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerMarkerTest extends MockeryTestCase
{
    /** @var  TransportManagerMarker */
    protected $sut;
    /** @var  m\MockInterface */
    private $mockQueryService;
    /** @var  m\MockInterface */
    private $mockAnnotationBuilderService;

    /** @var m\MockInterface|\Olcs\Service\Marker\MarkerService  */
    private $mockMarkerService;

    public function setUp(): void
    {
        $this->sut = new TransportManagerMarker();

        $this->mockQueryService = m::mock();
        $this->mockAnnotationBuilderService = m::mock();

        $this->sut->setAnnotationBuilderService($this->mockAnnotationBuilderService);
        $this->sut->setQueryService($this->mockQueryService);

        /** @var \Olcs\Service\Marker\MarkerService $mockMarkerService */
        $this->mockMarkerService = m::mock(\Olcs\Service\Marker\MarkerService::class);
        $this->sut->setMarkerService($this->mockMarkerService);
    }

    /**
     * Test attach
     *
     * @group transportManagerMarker
     */
    public function testAttach()
    {
        /** @var \Laminas\EventManager\EventManagerInterface $mockEventManager */
        $mockEventManager = m::mock(\Laminas\EventManager\EventManagerInterface::class)
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
    public function testCreateService()
    {
        /** @var m\MockInterface|ServiceLocatorInterface $mockSl */
        $mockSl = m::mock(ServiceLocatorInterface::class);

        $mockMarkerService = m::mock(\Olcs\Service\Marker\MarkerService::class);
        $mockQueryService = m::mock();
        $mockAnnotationBuilderService = m::mock();
        $mockApplicationService = m::mock();

        $mockSl->shouldReceive('get')->with(\Olcs\Service\Marker\MarkerService::class)->once()
            ->andReturn($mockMarkerService);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->once()
            ->andReturn($mockAnnotationBuilderService);
        $mockSl->shouldReceive('get')->with('QueryService')->once()->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with('Application')->once()->andReturn($mockApplicationService);

        $obj = $this->sut->createService($mockSl);

        $this->assertSame($mockAnnotationBuilderService, $obj->getAnnotationBuilderService());
        $this->assertSame($mockMarkerService, $obj->getMarkerService());
        $this->assertSame($mockQueryService, $obj->getQueryService());
        $this->assertSame($mockApplicationService, $obj->getApplicationService());

        $this->assertInstanceOf('Olcs\Listener\RouteParam\TransportManagerMarker', $obj);
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
            ->with('transportManager', array('result'=>'TM', 'results'=>'TM'))->once();

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

        $event = new RouteParam();
        $event->setValue(12);

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

        $event = new RouteParam();
        $event->setValue(18);

        $this->sut->onLicenceTransportManagerMarker($event);
    }

    public function testOnLicenceTransportManagerMarkerQueryError()
    {
        $this->mockQuery(['licence' => 18, 'transportManager' => null], false);

        $event = new RouteParam();
        $event->setValue(18);

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

        $event = new RouteParam();
        $event->setValue(534);

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

        $event = new RouteParam();
        $event->setValue(534);

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

        $event = new RouteParam();
        $event->setValue(534);

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

        $event = new RouteParam();
        $event->setValue(534);

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

        $event = new RouteParam();
        $event->setValue(534);

        $this->expectException(\RuntimeException::class);

        $this->sut->onApplicationTransportManagerMarker($event);
    }
}
