<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\BusRegAction;
use Mockery as m;
use Olcs\Listener\RouteParams;
use Common\Service\BusRegistration;

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

        $mockNavigation = m::mock('\StdClass');
        $mockNavigation->shouldReceive('setVisible')->times($expectedCallsToDisableButtons)->with(false);
        $mockNavigation->shouldReceive('setClass')->with('action--secondary js-modal-ajax');

        $mockRightSidebar = m::mock('\Zend\Navigation\Navigation');
        $mockRightSidebar->shouldReceive('findById')->times($expectedCallsToFindById)->andReturn($mockNavigation);

        $mockBusRegBusinessService = m::mock('Common\BusinessService\Service\Bus\BusReg');
        $mockBusRegBusinessService->shouldReceive('isGrantable')->with($busRegId)->andReturn($isGrantable);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('getServiceLocator')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockRightSidebar);
        $mockSl->shouldReceive('get')->with('BusinessServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Bus\BusReg')->andReturn($mockBusRegBusinessService);

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
                BusRegistration::STATUS_NEW,
                false,
                7,
                7
            ],
            [
                BusRegistration::STATUS_REGISTERED,
                false,
                10,
                10
            ],
            [
                BusRegistration::STATUS_VAR,
                false,
                8,
                7
            ],
            [
                BusRegistration::STATUS_CANCEL,
                false,
                7,
                7
            ],
            [
                BusRegistration::STATUS_CANCELLED,
                false,
                10,
                10
            ],
            [
                BusRegistration::STATUS_NEW,
                true,
                6,
                6
            ],
            [
                BusRegistration::STATUS_REGISTERED,
                true,
                10,
                10
            ],
            [
                BusRegistration::STATUS_VAR,
                true,
                7,
                6
            ],
            [
                BusRegistration::STATUS_CANCEL,
                true,
                6,
                6
            ],
            [
                BusRegistration::STATUS_CANCELLED,
                true,
                10,
                10
            ],
        ];
    }

    public function onBusRegProviderNonVariationOrCancellation()
    {
        return [
            [
                'foo',
                10,
                10
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
