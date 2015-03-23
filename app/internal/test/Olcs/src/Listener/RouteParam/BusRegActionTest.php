<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\BusRegAction;
use Mockery as m;
use Olcs\Listener\RouteParams;

/**
 * Class BusRegActionTest
 * @package OlcsTest\Listener\RouteParam
 */
class BusRegActionTest extends TestCase
{
    public function testAttach()
    {
        $sut = new BusRegAction();

        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'busRegId', [$sut, 'onBusRegAction'], 1);

        $sut->attach($mockEventManager);
    }

    /**
     * @dataProvider onBusRegProviderVariationOrCancellation
     * @param $status
     * @param $isGrantable
     * @param $expectedCallsToFindById
     * @param $expectedCallsToDisableButtons
     */
    public function testOnBusRegActionVariationOrCancellation(
        $status,
        $isGrantable,
        $expectedCallsToFindById,
        $expectedCallsToDisableButtons
    ) {
        $busRegId = 1;
        $busReg = ['id' => $busRegId, 'status' => ['id' => $status], 'shortNoticeRefused' => 'Y'];

        $event = new RouteParam();
        $event->setValue($busRegId);

        $mockBusRegService = m::mock('Common\Service\Data\BusReg');
        $mockBusRegService->shouldReceive('isLatestVariation')->with($busRegId)->andReturn(true);
        $mockBusRegService->shouldReceive('fetchOne')->with($busRegId)->andReturn($busReg);
        $mockBusRegService->shouldReceive('isGrantable')->with($busRegId)->andReturn($isGrantable);

        $mockNavigation = m::mock('\StdClass');
        $mockNavigation->shouldReceive('setVisible')->times($expectedCallsToDisableButtons)->with(false);
        $mockNavigation->shouldReceive('setClass')->with('action--secondary js-modal-ajax');

        $mockRightSidebar = m::mock('\Zend\Navigation\Navigation');
        $mockRightSidebar->shouldReceive('findById')->times($expectedCallsToFindById)->andReturn($mockNavigation);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('getServiceLocator')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockRightSidebar);

        $sut = new BusRegAction();
        $sut->setServiceLocator($mockSl);

        $sut->setBusRegService($mockBusRegService);

        $sut->onBusRegAction($event);
    }

    /**
     * @dataProvider onBusRegProviderNonVariationOrCancellation
     * @param string $status
     * @param int $expectedCallsToFindById
     * @param int $expectedCallsToDisableButtons
     */
    public function testOnBusRegActionNonVariationOrCancellation(
        $status,
        $expectedCallsToFindById,
        $expectedCallsToDisableButtons
    ) {
        $busRegId = 1;
        $busReg = ['id' => $busRegId, 'status' => ['id' => $status]];

        $event = new RouteParam();
        $event->setValue($busRegId);

        $mockBusRegService = m::mock('Common\Service\Data\BusReg');
        $mockBusRegService->shouldReceive('fetchOne')->with($busRegId)->andReturn($busReg);
        $mockBusRegService->shouldReceive('isLatestVariation')->with($busRegId)->andReturn(false);

        $mockNavigation = m::mock('\StdClass');
        $mockNavigation->shouldReceive('setVisible')->times($expectedCallsToDisableButtons)->with(false);
        $mockNavigation->shouldReceive('setClass')->with('action--secondary js-modal-ajax');

        $mockRightSidebar = m::mock('\Zend\Navigation\Navigation');
        $mockRightSidebar->shouldReceive('findById')->times($expectedCallsToFindById)->andReturn($mockNavigation);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('getServiceLocator')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockRightSidebar);

        $sut = new BusRegAction();
        $sut->setServiceLocator($mockSl);

        $sut->setBusRegService($mockBusRegService);

        $sut->onBusRegAction($event);
    }

    public function onBusRegProviderVariationOrCancellation()
    {
        return [
            [
                'breg_s_new',
                false,
                7,
                7
            ],
            [
                'breg_s_var',
                false,
                8,
                7
            ],
            [
                'breg_s_cancellation',
                false,
                6,
                6
            ],
            [
                'breg_s_new',
                true,
                6,
                6
            ],
            [
                'breg_s_var',
                true,
                7,
                6
            ],
            [
                'breg_s_cancellation',
                true,
                5,
                5
            ]

        ];
    }

    public function onBusRegProviderNonVariationOrCancellation()
    {
        return [
            [
                'foo',
                9,
                9
            ]
        ];
    }

    public function testCreateService()
    {
        $mockBusRegService = m::mock('Common\Service\Data\BusReg');
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($mockBusRegService);

        $sut = new BusRegAction();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockBusRegService, $sut->getBusRegService());
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
    }
}
