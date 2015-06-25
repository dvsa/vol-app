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

    /**
     * @dataProvider provideOnOrganisationTestData
     */
    public function testOnOrganisation($isIrfo)
    {
        $id = 1;
        $orgData = ['name' => 'org name'];

        $sut = new Organisation();

        $mockOrganisationEntityService = m::mock('Entity\Organisation');
        $mockOrganisationEntityService->shouldReceive('findByIdentifier')->once()->with($id)->andReturn($orgData);
        $mockOrganisationEntityService->shouldReceive('isIrfo')->once()->with($id)->andReturn($isIrfo);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('Entity\Organisation')->once()->andReturn($mockOrganisationEntityService);

        $event = new RouteParam();
        $event->setValue($id);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('append')->once()->with('org name');

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);

        $mockNavigation = m::mock('\StdClass');
        $mockNavigation->shouldReceive('setVisible')->times($isIrfo ? 0 : 1)->with(false);

        $mockMenu = m::mock('\Zend\Navigation\Navigation');
        $mockMenu->shouldReceive('__invoke')->with('navigation')->andReturnSelf();
        $mockMenu->shouldReceive('findById')->andReturn($mockNavigation);

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('Navigation')->andReturn($mockMenu);

        $sut->setServiceLocator($mockSl);
        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->onOrganisation($event);
    }

    public function provideOnOrganisationTestData()
    {
        return [
            // isIrfo: false
            [
                false
            ],
            // isIrfo: true
            [
                true
            ]
        ];
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);

        $sut = new Organisation();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
    }
}
