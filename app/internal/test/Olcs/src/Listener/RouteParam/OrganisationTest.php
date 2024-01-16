<?php

namespace OlcsTest\Listener\RouteParam;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Cqrs\Query\CachingQueryService as QueryService;
use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\Event;
use Laminas\View\HelperPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\Organisation;
use Olcs\Listener\RouteParams;
use Olcs\Service\Marker\MarkerService;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Navigation;

class OrganisationTest extends MockeryTestCase
{
    /** @var  m\MockInterface */
    private $mockAnnotationBldr;
    /** @var  m\MockInterface */
    private $mockQuerySrv;
    /** @var  m\MockInterface */
    private $mockMarkerSrv;
    /** @var  m\MockInterface */
    private $mockSideBar;
    /** @var  m\MockInterface */
    private $mocNavMenu;
    /** @var  m\MockInterface */
    private $mockResponse;

    /** @var  Organisation */
    protected $sut;

    public function setUp(): void
    {
        $this->mockMarkerSrv = m::mock(MarkerService::class);

        //  mock api response
        $this->mockResponse = m::mock();
        $query = m::mock(QueryContainerInterface::class);

        $this->mockAnnotationBldr = m::mock(AnnotationBuilder::class);
        $this->mockAnnotationBldr
            ->shouldReceive('createQuery')
            ->atMost(1)
            ->andReturn($query);

        $this->mockQuerySrv = m::mock(QueryService::class);
        $this->mockQuerySrv
            ->shouldReceive('send')
            ->with($query)
            ->atMost(1)
            ->andReturn($this->mockResponse);

        //  mock Navigation
        $this->mockSideBar = m::mock(AbstractContainer::class);
        $this->mocNavMenu = m::mock(AbstractContainer::class);

        $mockNavPlugin = m::mock(Navigation::class)
            ->shouldReceive('__invoke')
            ->with('navigation')
            ->andReturn($this->mocNavMenu)
            ->getMock();

        $helperMngr = m::mock(HelperPluginManager::class)
            ->shouldReceive('get')
            ->once()
            ->with('Navigation')
            ->andReturn($mockNavPlugin)
            ->getMock();

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')
            ->andReturnUsing(
                function ($class) use ($helperMngr) {
                    $map = [
                        'TransferAnnotationBuilder' => $this->mockAnnotationBldr,
                        'QueryService' => $this->mockQuerySrv,
                        'right-sidebar' => $this->mockSideBar,
                        MarkerService::class => $this->mockMarkerSrv,
                        'ViewHelperManager' => $helperMngr,
                    ];

                    return $map[$class];
                }
            );

        $this->sut = new Organisation();
        $this->sut->__invoke($mockSl, Organisation::class);

        parent::setUp();
    }

    private function setupOrganisation($orgData)
    {
        $this->mockResponse
            ->shouldReceive('isOk')->with()->once()->andReturn(true)
            ->shouldReceive('getResult')->with()->once()->andReturn($orgData);

        $this->mockMarkerSrv
            ->shouldReceive('addData')
            ->with('organisation', $orgData);
    }

    public function testAttach()
    {
        /** @var EventManagerInterface|m\MockInterface $mockEventManager */
        $mockEventManager = m::mock(EventManagerInterface::class);
        $mockEventManager
            ->shouldReceive('attach')
            ->once()
            ->with(RouteParams::EVENT_PARAM . 'organisation', [$this->sut, 'onOrganisation'], 1);

        $this->sut->attach($mockEventManager);
    }

    public function testOnOrganisationNotFound()
    {
        $id = 1;

        $this->mockResponse
            ->shouldReceive('isOk')
            ->andReturn(false);

        //  expect
        $this->expectException(ResourceNotFoundException::class);

        //  call
        $routeParam = new RouteParam();
        $routeParam->setValue($id);

        $event = new Event(null, $routeParam);

        $this->sut->onOrganisation($event);
    }

    public function testOnOrganisation()
    {
        $id = 1;

        $orgData = [
            'name' => 'org name',
            'isIrfo' => 'Y',
            'isDisqualified' => true,
            'isUnlicensed' => true,
        ];
        $this->setupOrganisation($orgData);

        $mockMenuItem = m::mock()
            ->shouldReceive('setVisible')
            ->with(false)
            ->getMock();

        $this->mocNavMenu
            ->shouldReceive('findById')
            ->with(m::pattern('/^operator_/'))
            ->times(5)
            ->andReturn($mockMenuItem);

        $this->mockSideBar
            ->shouldReceive('findById')
            ->with('operator-decisions-disqualify')
            ->once()
            ->andReturn($mockMenuItem);

        //  call
        $routeParam = new RouteParam();
        $routeParam->setValue($id);

        $event = new Event(null, $routeParam);

        $this->sut->onOrganisation($event);
    }

    public function testOnOrganisationIsNotAll()
    {
        $id = 1;
        $orgData = [
            'name' => 'org name',
            'isIrfo' => 'not Y',
            'isDisqualified' => false,
            'isUnlicensed' => false,
        ];
        $this->setupOrganisation($orgData);

        $mockMenuItem = m::mock()
            ->shouldReceive('setVisible')
            ->with(false)
            ->getMock();

        $this->mocNavMenu
            ->shouldReceive('findById')
            ->times(3)
            ->with(m::pattern('/^unlicensed_operator_/'))
            ->andReturn($mockMenuItem)
            //
            ->shouldReceive('findById')
            ->times(2)
            ->with(m::pattern('/^operator_/'))
            ->andReturn($mockMenuItem);

        $this->mockSideBar->shouldReceive('findById')->never();

        //  call
        $routeParam = new RouteParam();
        $routeParam->setValue($id);

        $event = new Event(null, $routeParam);

        $this->sut->onOrganisation($event);
    }
}
