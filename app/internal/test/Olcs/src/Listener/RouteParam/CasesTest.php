<?php


namespace OlcsTest\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Listener\RouteParam\Cases;
use Mockery as m;

/**
 * Class CasesTest
 * @package OlcsTest\Listener\RouteParam
 */
class CasesTest extends TestCase
{
    public function testAttach()
    {
        $sut = new Cases();

        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'case', [$sut, 'onCase'], 1);

        $sut->attach($mockEventManager);
    }

    public function testOnCase()
    {
        $caseId = 1;
        $case = ['id' => $caseId, 'closeDate' => '2014-01-01'];

        $status = ['colour' => 'Grey', 'value' => 'Closed'];

        $event = new RouteParam();
        $event->setValue($caseId);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('prepend')->with('Case ' . $caseId);
        $mockContainer->shouldReceive('append')->with('Case ' . $caseId);
        $mockContainer->shouldReceive('append')->with('Case subtitle');
        $mockContainer->shouldReceive('set')->with($case);

        $mockContainer->shouldReceive('set')->with($status);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('pageSubtitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('case')->andReturn($mockContainer);

        $mockPlaceholder->shouldReceive('getContainer')->with('status')->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('headTitle')->andReturn($mockContainer);

        $sut = new Cases();
        $sut->setCaseService($mockCaseService);
        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->onCase($event);
    }

    public function testOnCaseTriggersLicence()
    {
        $caseId = 1;
        $case = [
            'id' => $caseId,
            'licence' => ['id' => 4],
            'closeDate' => null
        ];

        $mockTarget = m::mock('Olcs\Listener\RouteParams');
        $mockTarget->shouldReceive('trigger')->with('licence', 4);

        $event = new RouteParam();
        $event->setValue($caseId);
        $event->setTarget($mockTarget);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldIgnoreMissing();

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->withAnyArgs()->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('headTitle')->andReturn($mockContainer);

        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockLicenceService->shouldReceive('setData')->with(4, ['id' => 4]);

        $sut = new Cases();
        $sut->setCaseService($mockCaseService);
        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setLicenceService($mockLicenceService);
        $sut->onCase($event);
    }

    public function testOnCaseSetsDataButDoesNotTriggerLicence()
    {
        $caseId = 1;
        $case = [
            'id' => $caseId,
            'licence' => ['id' => 4],
            'closeDate' => null
        ];

        $mockTarget = m::mock('Olcs\Listener\RouteParams');

        $event = new RouteParam();
        $event->setValue($caseId);
        $event->setTarget($mockTarget);
        $event->setContext(['licence' => 7]);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldIgnoreMissing();

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->withAnyArgs()->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('headTitle')->andReturn($mockContainer);

        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockLicenceService->shouldReceive('setData')->with(4, ['id' => 4]);

        $sut = new Cases();
        $sut->setCaseService($mockCaseService);
        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setLicenceService($mockLicenceService);
        $sut->onCase($event);
    }

    public function testCreateService()
    {
        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockViewHelperManager = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseService);
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Licence')->andReturn($mockLicenceService);

        $sut = new Cases();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockCaseService, $sut->getCaseService());
        $this->assertSame($mockLicenceService, $sut->getLicenceService());
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
    }
}
