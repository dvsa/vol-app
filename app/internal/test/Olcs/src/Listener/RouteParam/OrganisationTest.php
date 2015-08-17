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
    public function testAttach()
    {
        $sut = new Organisation();

        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'organisation', [$sut, 'onOrganisation'], 1);

        $sut->attach($mockEventManager);
    }

    public function testOnOrganisationNotFound()
    {
        $id = 1;

        $sut = new Organisation();

        $mockAnnotationBuilder = m::mock();
        $sut->setAnnotationBuilder($mockAnnotationBuilder);
        $mockQueryService = m::mock();
        $sut->setQueryService($mockQueryService);

        $mockResponse = m::mock();
        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturn('CREATE_QUERY');
        $mockQueryService->shouldReceive('send')->with('CREATE_QUERY')->once()->andReturn($mockResponse);
        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(false);

        $event = new RouteParam();
        $event->setValue($id);

        $this->setExpectedException(\Common\Exception\ResourceNotFoundException::class);

        $sut->onOrganisation($event);
    }

    public function testOnOrganisationNotIrfoDisqualified()
    {
        $id = 1;
        $orgData = [
            'name' => 'org name',
            'isIrfo' => 'N',
            'isDisqualified' => true,
        ];

        $sut = new Organisation();

        $mockAnnotationBuilder = m::mock();
        $sut->setAnnotationBuilder($mockAnnotationBuilder);
        $mockQueryService = m::mock();
        $sut->setQueryService($mockQueryService);
        $mockSideBar = m::mock();
        $sut->setSidebarNavigationService($mockSideBar);
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $sut->setViewHelperManager($mockViewHelperManager);

        $mockResponse = m::mock();
        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturn('CREATE_QUERY');
        $mockQueryService->shouldReceive('send')->with('CREATE_QUERY')->once()->andReturn($mockResponse);
        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResponse->shouldReceive('getResult')->with()->once()->andReturn($orgData);

        $event = new RouteParam();
        $event->setValue($id);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('append')->once()->with('org name');
        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $mockNavigation = m::mock('\StdClass');
        $mockNavigation->shouldReceive('setVisible')->times(1)->with(false);

        $mockMenu = m::mock('\Zend\Navigation\Navigation');
        $mockMenu->shouldReceive('__invoke')->with('navigation')->andReturnSelf();
        $mockMenu->shouldReceive('findById')->andReturn($mockNavigation);
        $mockViewHelperManager->shouldReceive('get')->with('Navigation')->andReturn($mockMenu);

        $mockNav = m::mock();
        $mockNav->shouldReceive('setVisible')->once();
        $mockSideBar->shouldReceive('findById')->with('operator-decisions-disqualify')->once()->andReturn($mockNav);

        $sut->onOrganisation($event);
    }

    public function testOnOrganisationIrfoNotDisqualified()
    {
        $id = 1;
        $orgData = [
            'name' => 'org name',
            'isIrfo' => 'Y',
            'isDisqualified' => false,
        ];

        $sut = new Organisation();

        $mockAnnotationBuilder = m::mock();
        $sut->setAnnotationBuilder($mockAnnotationBuilder);
        $mockQueryService = m::mock();
        $sut->setQueryService($mockQueryService);
        $mockSideBar = m::mock();
        $sut->setSidebarNavigationService($mockSideBar);
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $sut->setViewHelperManager($mockViewHelperManager);

        $mockResponse = m::mock();
        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturn('CREATE_QUERY');
        $mockQueryService->shouldReceive('send')->with('CREATE_QUERY')->once()->andReturn($mockResponse);
        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResponse->shouldReceive('getResult')->with()->once()->andReturn($orgData);

        $event = new RouteParam();
        $event->setValue($id);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('append')->once()->with('org name');
        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $sut->onOrganisation($event);
    }


    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn('TransferAnnotationBuilder');
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn('QueryService');
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn('right-sidebar');

        $sut = new Organisation();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame('TransferAnnotationBuilder', $sut->getAnnotationBuilder());
        $this->assertSame('QueryService', $sut->getQueryService());
        $this->assertSame('right-sidebar', $sut->getSidebarNavigationService());
    }
}
