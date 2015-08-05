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

    public function setUp()
    {
        parent::setup();
        $this->sut = new Licence();
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'licence', [$this->sut, 'onLicence'], 1);

        $this->sut->attach($mockEventManager);
    }

    /**
     * Common setup for:
     *  testOnLicenceWithValidGoodsLicence
     *  testOnLicenceWithValidPsvLicence
     *  testOnLicenceWithNotSubmittedPsvSpecialRestricted
     */
    protected function onLicenceSetup($licenceId, $licence, $pendingChanges = false)
    {
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
            ->andReturn($pendingChanges);

        $this->sut->setViewHelperManager($mockViewHelperManager);
        $this->sut->setLicenceService($mockLicenceService);
        $this->sut->setLicenceStatusService($mockLicenceStatusService);
        $this->sut->setLicenceStatusHelperService($mockLicenceStatusHelperService);
        $this->sut->setRouter($mockRouter);
    }

    public function testOnLicenceWithValidGoodsLicence()
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

        $this->onLicenceSetup($licenceId, $licence);

        // 'terminate' should be hidden for Goods vehicles
        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);

        $event = new RouteParam();
        $event->setValue($licenceId);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithValidPsvLicence()
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
                'id' => LicenceEntityService::LICENCE_CATEGORY_PSV
            ],
        ];

        $this->onLicenceSetup($licenceId, $licence);

        // 'surrender' should be hidden for Goods vehicles
        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);

        $event = new RouteParam();
        $event->setValue($licenceId);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithTerminatedPsvLicence()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => LicenceEntityService::LICENCE_STATUS_TERMINATED
            ],
            'goodsOrPsv' => [
                'id' => LicenceEntityService::LICENCE_CATEGORY_PSV
            ],
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-create-variation');
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-print-licence');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail');
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke');
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);

        $event = new RouteParam();
        $event->setValue($licenceId);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithSurrenderedGoodsLicence()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => LicenceEntityService::LICENCE_STATUS_SURRENDERED
            ],
            'goodsOrPsv' => [
                'id' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-create-variation');
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-print-licence');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail');
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke');
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);

        $event = new RouteParam();
        $event->setValue($licenceId);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithNotSubmittedPsvSpecialRestricted()
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

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-create-variation');
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-print-licence');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail');
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke');
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);

        $event = new RouteParam();
        $event->setValue($licenceId);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithValidGoodsLicenceAndPendingChange()
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

        $this->onLicenceSetup($licenceId, $licence, true);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail');
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke');
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);

        $event = new RouteParam();
        $event->setValue($licenceId);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithValidPsvLicenceAndPendingChange()
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
                'id' => LicenceEntityService::LICENCE_CATEGORY_PSV
            ],
        ];

        $this->onLicenceSetup($licenceId, $licence, true);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail');
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke');
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);

        $event = new RouteParam();
        $event->setValue($licenceId);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithRevokedLicence()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => LicenceEntityService::LICENCE_STATUS_REVOKED
            ],
            'goodsOrPsv' => [
                'id' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
        ];

        $this->onLicenceSetup($licenceId, $licence);

        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-create-variation');
        $this->mockHideButton($mockSidebar, 'licence-quick-actions-print-licence');
        $this->mockHideButton($mockSidebar, 'licence-decisions-curtail');
        $this->mockHideButton($mockSidebar, 'licence-decisions-revoke');
        $this->mockHideButton($mockSidebar, 'licence-decisions-suspend');
        $this->mockHideButton($mockSidebar, 'licence-decisions-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->sut->setNavigationService($mockSidebar);

        $event = new RouteParam();
        $event->setValue($licenceId);

        $this->sut->onLicence($event);
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockLicenceStatusService = m::mock('Common\Service\Entity\LicenceStatusRuleEntityService');
        $mockLicenceStatusHelperService = m::mock('Common\Service\Helper\LicenceStatusHelperService');
        $mockNavigation = m::mock(); // 'right-sidebar'
        $mockRouter = m::mock('Zend\Mvc\Router\RouteStackInterface');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Licence')->andReturn($mockLicenceService);
        $mockSl->shouldReceive('get')->with('Entity\LicenceStatusRule')->andReturn($mockLicenceStatusService);
        $mockSl->shouldReceive('get')->with('Helper\LicenceStatus')->andReturn($mockLicenceStatusHelperService);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockNavigation);
        $mockSl->shouldReceive('get')->with('Router')->andReturn($mockRouter);

        $sut = new Licence();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockLicenceService, $sut->getLicenceService());
        $this->assertSame($mockLicenceStatusService, $sut->getLicenceStatusService());
        $this->assertSame($mockLicenceStatusHelperService, $sut->getLicenceStatusHelperService());
        $this->assertSame($mockNavigation, $sut->getNavigationService());
        $this->assertSame($mockRouter, $sut->getRouter());
    }

    /**
     * Set an expectation that a given nav button should be hidden
     *
     * @param Mockery\Mock $mockSidebar
     * @param string $navId
     * @param int $times
     */
    protected function mockHideButton($mockSidebar, $navId)
    {
        $mockSidebar
            ->shouldReceive('findById')
            ->with($navId)
            ->andReturn(
                m::mock()
                    ->shouldReceive('setVisible')
                    ->with(0)
                    ->once()
                    ->getMock()
            );
    }
}
