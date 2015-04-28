<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as MockeryTestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\Operator;
use Mockery as m;
use Olcs\Listener\RouteParams;

/**
 * Class OperatorTest
 * @package OlcsTest\Listener\RouteParam
 */
class OperatorTest extends MockeryTestCase
{
    public function testAttach()
    {
        $sut = new Operator();

        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'operator', [$sut, 'onOperator'], 1);

        $sut->attach($mockEventManager);
    }

    /**
     * @dataProvider provideOnOperatorTestData
     */
    public function testOnOperator($isIrfo)
    {
        $operatorId = 1;

        $sut = new Operator();

        $mockOrganisationEntityService = m::mock('Entity\Organisation');
        $mockOrganisationEntityService->shouldReceive('isIrfo')->once()->with($operatorId)->andReturn($isIrfo);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('Entity\Organisation')->once()->andReturn($mockOrganisationEntityService);

        $event = new RouteParam();
        $event->setValue($operatorId);

        $mockNavigation = m::mock('\StdClass');
        $mockNavigation->shouldReceive('setVisible')->times($isIrfo ? 0 : 1)->with(false);

        $mockMenu = m::mock('\Zend\Navigation\Navigation');
        $mockMenu->shouldReceive('__invoke')->with('navigation')->andReturnSelf();
        $mockMenu->shouldReceive('findById')->andReturn($mockNavigation);

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('Navigation')->andReturn($mockMenu);

        $sut->setServiceLocator($mockSl);
        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->onOperator($event);
    }

    public function provideOnOperatorTestData()
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

        $sut = new Operator();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
    }
}
