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
    protected $sut;

    protected $sm;

    public function setUp()
    {
        parent::setup();
        $this->sm = Bootstrap::getServiceManager();
        $this->sut = new Licence();
        $this->sut->setServiceLocator($this->sm);

        $this->sm->setService('right-sidebar', m::mock());
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'licence', [$this->sut, 'onLicence'], 1);

        $this->sut->attach($mockEventManager);
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
            ],
            'goodsOrPsv' => [
                'id' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
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

        $mockLicenceStatusHelperService = m::mock('Common\Service\Helper\LicenceStatusHelperService');
        $mockLicenceStatusHelperService->shouldReceive('hasQueuedRevocationCurtailmentSuspension')
            ->andReturn(false);
        $this->sm->setService('Helper\LicenceStatus', $mockLicenceStatusHelperService);

        // terminate should be hidden for Goods vehicles
        $mockSidebar = m::mock();
        $this->sm->setService('right-sidebar', $mockSidebar);
        $mockSidebar
            ->shouldReceive('findById')
            ->with('licence-decisions-terminate')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(0)
                    ->getMock()
            );

        $this->sut->setLicenceStatusService($mockLicenceStatusService);
        $this->sut->setLicenceService($mockLicenceService);
        $this->sut->setRouter($mockRouter);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $this->sut->onLicence($event);
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
            'goodsOrPsv' => [
                'id' => LicenceEntityService::LICENCE_CATEGORY_PSV
            ],
            'status' => [
                'id' => LicenceEntityService::LICENCE_STATUS_NOT_SUBMITTED
            ]
        ];

        $event = new RouteParam();
        $event->setValue($licenceId);

        $mockSidebar = m::mock();

        $this->sm->setService('right-sidebar', $mockSidebar);

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
            ->with('licence-decisions-suspend')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(0)
                    ->getMock()
            )
            ->with('licence-decisions-terminate')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(0)
                    ->getMock()
            )
            ->shouldReceive('findById')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(0)
                    ->getMock()
            );

        $this->sut->setLicenceService($mockLicenceService);
        $this->sut->setLicenceStatusService($mockLicenceStatusService);
        $this->sut->setRouter($mockRouter);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $this->sut->onLicence($event);
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
