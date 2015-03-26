<?php

namespace OlcsTest\Listener\RouteParam;

use Common\Service\Entity\LicenceStatusRuleEntityService;
use OlcsTest\Bootstrap;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\Licence;
use Mockery as m;
use Olcs\Listener\RouteParams;
use Common\Service\Entity\LicenceEntityService;

/**
 * Class LicenceTest
 * @package OlcsTest\Listener\RouteParam
 */
class LicenceTest extends TestCase
{
    public function testAttach()
    {
        $sut = new Licence();

        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'licence', [$sut, 'onLicence'], 1);

        $sut->attach($mockEventManager);
    }

    public function testOnLicence()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => LicenceEntityService::LICENCE_STATUS_VALID
            ]
        ];

        $event = new RouteParam();
        $event->setValue($licenceId);

        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockLicenceService->shouldReceive('fetchLicenceData')->with($licenceId)->andReturn($licence);
        $mockLicenceService->shouldReceive('setId')->with($licenceId);

        $mockRouter = m::mock('Zend\Mvc\Router\RouteStackInterface');
        $mockRouter->shouldReceive('assemble')
            ->with(['licence' => $licenceId], ['name' => 'licence/cases'])
            ->andReturn('http://licence-url/');

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('prepend')->with('<a href="http://licence-url/">L2347137</a>');
        $mockContainer->shouldReceive('set')->with($licence);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('licence')->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $mockLicenceStatusService = m::mock('Common\Service\Entity\LicenceStatusRuleEntityService');
        $mockLicenceStatusService->shouldReceive('getPendingChangesForLicence');

        $sut = new Licence();
        $sut->setLicenceStatusService($mockLicenceStatusService);
        $sut->setLicenceService($mockLicenceService);
        $sut->setRouter($mockRouter);
        $sut->setViewHelperManager($mockViewHelperManager);

        $sut->onLicence($event);
    }

    public function testOnLicenceWithSpecialRestrictedAndNotSubmitted()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED
            ],
            'status' => [
                'id' => LicenceEntityService::LICENCE_STATUS_NOT_SUBMITTED
            ]
        ];

        $event = new RouteParam();
        $event->setValue($licenceId);

        $mockSidebar = m::mock();

        $sm = Bootstrap::getServiceManager();
        $sm->setService('right-sidebar', $mockSidebar);

        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockLicenceService->shouldReceive('fetchLicenceData')->with($licenceId)->andReturn($licence);
        $mockLicenceService->shouldReceive('setId')->with($licenceId);

        $mockRouter = m::mock('Zend\Mvc\Router\RouteStackInterface');
        $mockRouter->shouldReceive('assemble')
            ->with(['licence' => $licenceId], ['name' => 'licence/cases'])
            ->andReturn('http://licence-url/');

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('prepend')->with('<a href="http://licence-url/">L2347137</a>');
        $mockContainer->shouldReceive('set')->with($licence);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('licence')->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $mockLicenceStatusService = m::mock('Common\Service\Entity\LicenceStatusRuleEntityService');
        $mockLicenceStatusService->shouldReceive('getPendingChangesForLicence')
            ->andReturn(
                array(
                    'Count' => 1
                )
            );

        $mockSidebar->shouldReceive('findById')
            ->with('licence-quick-actions-create-variation')
            ->andReturn(
                m::mock()
                ->shouldReceive('setVisible')
                ->with(0)
                ->getMock()
            )
            ->shouldReceive('findById')
            ->with('licence-quick-actions-print-licence')
            ->andReturn(
                m::mock()
                ->shouldReceive('setVisible')
                ->with(0)
                ->getMock()
            )
            ->shouldReceive('findById')
            ->with('licence-decisions-curtail')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(0)
                    ->getMock()
            )
            ->shouldReceive('findById')
            ->with('licence-decisions-revoke')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(0)
                    ->getMock()
            )
            ->shouldReceive('findById')
            ->with('licence-decisions-suspend')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(0)
                    ->getMock()
            );

        $sut = new Licence();
        $sut->setLicenceService($mockLicenceService);
        $sut->setLicenceStatusService($mockLicenceStatusService);
        $sut->setRouter($mockRouter);
        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setServiceLocator($sm);

        $sut->onLicence($event);
    }

    public function testCreateService()
    {
        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockLicenceStatusRule = m::mock('Common\Service\Entity\LicenceStatusRuleEntityService');
        $mockRouter = m::mock('Zend\Mvc\Router\RouteStackInterface');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Licence')->andReturn($mockLicenceService);
        $mockSl->shouldReceive('get')->with('Entity\LicenceStatusRule')->andReturn($mockLicenceStatusRule);
        $mockSl->shouldReceive('get')->with('Router')->andReturn($mockRouter);

        $sut = new Licence();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockRouter, $sut->getRouter());
        $this->assertSame($mockLicenceService, $sut->getLicenceService());
        $this->assertSame($mockLicenceStatusRule, $sut->getLicenceStatusService());
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
    }
}
