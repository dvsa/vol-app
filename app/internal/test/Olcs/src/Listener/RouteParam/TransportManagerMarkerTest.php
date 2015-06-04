<?php

/**
 * Transport Manager Markers Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Olcs\Listener\RouteParam\TransportManagerMarker;
use Mockery as m;
use Olcs\Listener\RouteParams;
use Olcs\Event\RouteParam;

/**
 * Transport Manager Markers Service Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerMarkerTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new TransportManagerMarker();
        $this->sm = Bootstrap::getServiceManager();
    }

    /**
     * Test attach
     *
     * @group transportManagerMarker
     */
    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface')
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
        $this->sm->setService(
            'ViewHelperManager',
            m::mock()
        );
        $this->sm->setService(
            'Entity\TransportManager',
            m::mock()
        );
        $this->sm->setService(
            'Entity\TransportManagerLicence',
            m::mock()
        );
        $this->sm->setService(
            'Entity\TransportManagerApplication',
            m::mock()
        );
        $this->sm->setService(
            'Olcs\Service\Marker\MarkerPluginManager',
            m::mock()
            ->shouldReceive('get')
            ->with('Olcs\Service\Marker\TransportManagerMarkers')
            ->andReturn(m::mock())
            ->once()
            ->getMock()
        );
        $this->assertInstanceOf(
            'Olcs\Listener\RouteParam\TransportManagerMarker',
            $this->sut->createService($this->sm)
        );
    }

    /**
     * Test on transport manager marker
     *
     * @group transportManagerMarker
     */
    public function testOnTransportManagerMarker()
    {
        $transportManagerId = 1;

        $mockViewHelperManager = m::mock()
            ->shouldReceive('get')
            ->with('placeholder')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContainer')
                ->with('tmMarkers')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('set')
                    ->with('markers')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockTransportManagerMarkerService = m::mock()
            ->shouldReceive('generateMarkerTypes')
            ->with(['transportManager'], ['transportManager' => ['transportManager' => 'foo']])
            ->andReturn('markers')
            ->once()
            ->getMock();
        $this->sut->setTransportManagerMarkerService($mockTransportManagerMarkerService);

        $mockTransportManagerService = m::mock()
            ->shouldReceive('getTmForMarkers')
            ->with($transportManagerId)
            ->andReturn('foo')
            ->once()
            ->getMock();
        $this->sut->setTransportManagerService($mockTransportManagerService);

        $event = new RouteParam();
        $event->setValue($transportManagerId);

        $this->sut->onTransportManagerMarker($event);
    }

    /**
     * Test on licence transport manager marker
     *
     * @group transportManagerMarker
     */
    public function testOnLicenceTransportManagerMarker()
    {
        $licenceId = 1;

        $mockViewHelperManager = m::mock()
            ->shouldReceive('get')
            ->with('placeholder')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContainer')
                ->with('tmMarkers')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('set')
                    ->with('markers')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockTransportManagerMarkerService = m::mock()
            ->shouldReceive('generateMarkerTypes')
            ->with(['licenceTransportManagers'], ['licenceTransportManagers' => ['licenceTransportManagers' => 'foo']])
            ->andReturn('markers')
            ->once()
            ->getMock();
        $this->sut->setTransportManagerMarkerService($mockTransportManagerMarkerService);

        $mockTransportManagerLicenceService = m::mock()
            ->shouldReceive('getTmForLicence')
            ->with($licenceId)
            ->andReturn(['Results' => 'foo'])
            ->once()
            ->getMock();
        $this->sut->setTransportManagerLicenceService($mockTransportManagerLicenceService);

        $event = new RouteParam();
        $event->setValue($licenceId);

        $this->sut->onLicenceTransportManagerMarker($event);
    }

    /**
     * Test on application transport manager marker
     *
     * @group transportManagerMarker
     */
    public function testOnApplicationTransportManagerMarker()
    {
        $applicationId = 1;

        $mockViewHelperManager = m::mock()
            ->shouldReceive('get')
            ->with('placeholder')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContainer')
                ->with('tmMarkers')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('set')
                    ->with('markers')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockTransportManagerMarkerService = m::mock()
            ->shouldReceive('generateMarkerTypes')
            ->with(
                ['applicationTransportManagers'],
                ['applicationTransportManagers' => ['applicationTransportManagers' => 'foo']]
            )
            ->andReturn('markers')
            ->once()
            ->getMock();
        $this->sut->setTransportManagerMarkerService($mockTransportManagerMarkerService);

        $mockTransportManagerApplicationService = m::mock()
            ->shouldReceive('getTmForApplication')
            ->with($applicationId)
            ->andReturn(['Results' => 'foo'])
            ->once()
            ->getMock();
        $this->sut->setTransportManagerApplicationService($mockTransportManagerApplicationService);

        $event = new RouteParam();
        $event->setValue($applicationId);

        $this->sut->onApplicationTransportManagerMarker($event);
    }
}
