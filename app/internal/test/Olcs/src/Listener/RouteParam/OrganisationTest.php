<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as MockeryTestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\Organisation;
use Mockery as m;
use Olcs\Listener\RouteParams;

/**
 * Class OrganisationTest
 * @package OlcsTest\Listener\RouteParam
 */
class OrganisationTest extends MockeryTestCase
{
    public function setUp()
    {
        $this->sut = new Organisation();
        parent::setUp();
    }

    public function setupOrganisation($orgData)
    {
        $mockAnnotationBuilder = m::mock();
        $this->sut->setAnnotationBuilder($mockAnnotationBuilder);
        $mockQueryService = m::mock();
        $this->sut->setQueryService($mockQueryService);
        $mockMarkerService = m::mock(\Olcs\Service\Marker\MarkerService::class);
        $this->sut->setMarkerService($mockMarkerService);

        $mockResponse = m::mock();
        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturn('CREATE_QUERY');
        $mockQueryService->shouldReceive('send')->with('CREATE_QUERY')->once()->andReturn($mockResponse);
        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResponse->shouldReceive('getResult')->with()->once()->andReturn($orgData);

        $mockMarkerService->shouldReceive('addData')->with('organisation', $orgData);
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'organisation', [$this->sut, 'onOrganisation'], 1);

        $this->sut->attach($mockEventManager);
    }

    public function testOnOrganisationNotFound()
    {
        $id = 1;

        $mockAnnotationBuilder = m::mock();
        $this->sut->setAnnotationBuilder($mockAnnotationBuilder);
        $mockQueryService = m::mock();
        $this->sut->setQueryService($mockQueryService);

        $mockResponse = m::mock();
        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturn('CREATE_QUERY');
        $mockQueryService->shouldReceive('send')->with('CREATE_QUERY')->once()->andReturn($mockResponse);
        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(false);

        $event = new RouteParam();
        $event->setValue($id);

        $this->setExpectedException(\Common\Exception\ResourceNotFoundException::class);

        $this->sut->onOrganisation($event);
    }

    public function testOnOrganisationNotIrfoDisqualified()
    {
        $id = 1;
        $orgData = [
            'name' => 'org name',
            'isIrfo' => 'N',
            'isDisqualified' => true,
            'isUnlicensed' => true
        ];

        $mockSideBar = m::mock();
        $this->sut->setSidebarNavigationService($mockSideBar);
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $this->setupOrganisation($orgData);

        $event = new RouteParam();
        $event->setValue($id);

        $mockNavigation = m::mock('\StdClass');
        $mockNavigation->shouldReceive('setVisible')->times(6)->with(false);

        $mockMenu = m::mock('\Zend\Navigation\Navigation');
        $mockMenu->shouldReceive('__invoke')->with('navigation')->andReturnSelf();
        $mockMenu->shouldReceive('findById')->andReturn($mockNavigation);
        $mockViewHelperManager->shouldReceive('get')->with('Navigation')->andReturn($mockMenu);

        $mockNav = m::mock();
        $mockNav->shouldReceive('setVisible')->once();
        $mockSideBar->shouldReceive('findById')->with('operator-decisions-disqualify')->once()->andReturn($mockNav);

        $this->sut->onOrganisation($event);
    }

    public function testOnOrganisationIrfoNotDisqualified()
    {
        $id = 1;
        $orgData = [
            'name' => 'org name',
            'isIrfo' => 'Y',
            'isDisqualified' => false,
            'isUnlicensed' => false
        ];

        $mockSideBar = m::mock();
        $this->sut->setSidebarNavigationService($mockSideBar);
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockNavigation = m::mock('\StdClass');
        $mockNavigation->shouldReceive('setVisible')->times(3)->with(false);

        $mockMenu = m::mock('\Zend\Navigation\Navigation');
        $mockMenu->shouldReceive('__invoke')->with('navigation')->andReturnSelf();
        $mockMenu->shouldReceive('findById')->andReturn($mockNavigation);
        $mockViewHelperManager->shouldReceive('get')->with('Navigation')->andReturn($mockMenu);

        $this->setupOrganisation($orgData);

        $event = new RouteParam();
        $event->setValue($id);

        $this->sut->onOrganisation($event);
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockMarkerService = m::mock(\Olcs\Service\Marker\MarkerService::class);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn('TransferAnnotationBuilder');
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn('QueryService');
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn('right-sidebar');
        $mockSl->shouldReceive('get')->with(\Olcs\Service\Marker\MarkerService::class)->andReturn($mockMarkerService);

        $service = $this->sut->createService($mockSl);

        $this->assertSame($this->sut, $service);
        $this->assertSame($mockViewHelperManager, $this->sut->getViewHelperManager());
        $this->assertSame('TransferAnnotationBuilder', $this->sut->getAnnotationBuilder());
        $this->assertSame('QueryService', $this->sut->getQueryService());
        $this->assertSame('right-sidebar', $this->sut->getSidebarNavigationService());
        $this->assertSame($mockMarkerService, $this->sut->getMarkerService());
    }
}
