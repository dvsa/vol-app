<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\Marker;
use Mockery as m;
use Olcs\Listener\RouteParams;

/**
 * Class MarkerTest
 * @package OlcsTest\Listener\RouteParam
 */
class MarkerTest extends TestCase
{
    public function testAttach()
    {
        $sut = new Marker();

        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'case', [$sut, 'onCase'], 1);

        $sut->attach($mockEventManager);
    }

    public function testOnCase()
    {
        $caseId = 1;
        $case = ['id' => $caseId];
        $markers = ['stay' => 'No markers', 'appeal' => 'There is a pending appeal'];

        $event = new RouteParam();
        $event->setValue($caseId);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('set')->with($markers);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('markers')->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $mockCaseMarkerService = m::mock('Olcs\Service\Marker\CaseMarkers');
        $mockCaseMarkerService->shouldReceive('generateMarkerTypes')
            ->with(['appeal', 'stay'], ['case' => $case])
            ->andReturn($markers);

        $sut = new Marker();
        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setCaseMarkerService($mockCaseMarkerService);
        $sut->setCaseService($mockCaseService);

        $sut->onCase($event);
    }

    public function testCreateService()
    {
        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseMarkerService = m::mock('Olcs\Service\Marker\CaseMarkers');
        $mockViewHelperManager = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\Service\Marker\MarkerPluginManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseService);
        $mockSl->shouldReceive('get')->with('Olcs\Service\Marker\CaseMarkers')->andReturn($mockCaseMarkerService);

        $sut = new Marker();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockCaseService, $sut->getCaseService());
        $this->assertSame($mockCaseMarkerService, $sut->getCaseMarkerService());
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
    }
}
