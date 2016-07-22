<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\Licence;
use Mockery as m;
use Olcs\Listener\RouteParams;
use Common\RefData;

/**
 * Class LicenceTest
 * @package OlcsTest\Listener\RouteParam
 */
class LicenceTest extends TestCase
{
    /**
     * @var Licence
     */
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

    protected function onLicenceSetup($licenceId, $licenceData)
    {
        $mockAnnotationBuilder = m::mock();
        $this->sut->setAnnotationBuilderService($mockAnnotationBuilder);

        $mockQueryService = m::mock();
        $this->sut->setQueryService($mockQueryService);

        $mockResult = m::mock();

        $mockMarkerService = m::mock(\Olcs\Service\Marker\MarkerService::class);
        $this->sut->setMarkerService($mockMarkerService);

        $mockLicenceService = m::mock();
        $this->sut->setLicenceService($mockLicenceService);

        $mockViewHelperManager = m::mock(\Zend\View\HelperPluginManager::class);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($licenceId) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\Licence\Licence::class, $dto);
                $this->assertSame(['id' => $licenceId], $dto->getArrayCopy());
                return 'QUERY';
            }
        );

        $mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResult);

        if ($licenceData === false) {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);
        } else {
            $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
            $mockResult->shouldReceive('getResult')->with()->once()->andReturn($licenceData);

            $mockMarkerService->shouldReceive('addData')->with('licence', $licenceData)->once();
            $mockMarkerService->shouldReceive('addData')->with('continuationDetail', $licenceData['continuationMarker'])
                ->once();
            $mockMarkerService->shouldReceive('addData')->with('organisation', $licenceData['organisation'])->once();
            $mockMarkerService->shouldReceive('addData')->with('cases', $licenceData['cases'])->once();

            $mockLicenceService->shouldReceive('setId')->with($licenceId);

            $mockViewHelperManager->shouldReceive('get->getContainer->set')->with($licenceData)->once();
            $mockViewHelperManager->shouldReceive('get->getContainer->set')->with('latest note')->once();
        }
    }

    public function testOnLicenceQueryError()
    {
        $this->onLicenceSetup(32, false);
        $event = new RouteParam();
        $event->setValue(32);

        $this->setExpectedException(\RuntimeException::class);

        $this->sut->onLicence($event);
    }

    public function testOnLicenceWithValidGoodsLicence()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137',
            'licenceType' => [
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_VALID
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'latestNote' => ['comment' => 'latest note']
        ];

        $this->onLicenceSetup($licenceId, $licence);

        // 'terminate' should be hidden for Goods vehicles
        $mockSidebar = m::mock();
        $this->mockHideButton($mockSidebar, 'licence-decisions-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-surrender');
        $this->mockHideButton($mockSidebar, 'licence-decisions-undo-terminate');
        $this->mockHideButton($mockSidebar, 'licence-decisions-reset-to-valid');
        $this->sut->setNavigationService($mockSidebar);

        $mainNav = m::mock();
        $mainNav->shouldReceive('findOneBy->setVisible')->with(0);

        $this->sut->setMainNavigationService($mainNav);

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
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_VALID
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_PSV
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'latestNote' => ['comment' => 'latest note']
        ];

        $this->onLicenceSetup($licenceId, $licence);

        // 'surrender' should be hidden for Goods vehicles
        $mockSidebar = m::mock();
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
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_TERMINATED
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_PSV
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'latestNote' => ['comment' => 'latest note']
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
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_SURRENDERED
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'latestNote' => ['comment' => 'latest note']
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

        $mainNav = m::mock();
        $mainNav->shouldReceive('findOneBy->setVisible')->with(0);

        $this->sut->setMainNavigationService($mainNav);

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
                'id' => RefData::LICENCE_TYPE_SPECIAL_RESTRICTED
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_PSV
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_NOT_SUBMITTED
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'latestNote' => ['comment' => 'latest note']
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
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_VALID
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [],
            'licenceStatusRules' => [
                [
                    'startProcessedDate' => null,
                    'licenceStatus' => ['id' => 'lsts_suspended'],
                ]
            ],
            'latestNote' => ['comment' => 'latest note']
        ];

        $this->onLicenceSetup($licenceId, $licence);

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

        $mainNav = m::mock();
        $mainNav->shouldReceive('findOneBy->setVisible')->with(0);

        $this->sut->setMainNavigationService($mainNav);

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
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_VALID
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_PSV
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [
                [
                    'startProcessedDate' => '',
                    'licenceStatus' => ['id' => 'lsts_suspended'],
                ]
            ],
            'latestNote' => ['comment' => 'latest note']
        ];

        $this->onLicenceSetup($licenceId, $licence);

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
                'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
            ],
            'status' => [
                'id' => RefData::LICENCE_STATUS_REVOKED
            ],
            'goodsOrPsv' => [
                'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
            ],
            'continuationMarker' => 'CONTINUATION_MARKER',
            'organisation' => 'ORGANISATION',
            'cases' => 'CASES',
            'licenceStatusRules' => [
                [
                    'startProcessedDate' => '2013-12-12',
                    'licenceStatus' => ['id' => 'lsts_suspended'],
                ]
            ],
            'latestNote' => ['comment' => 'latest note']
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

        $mainNav = m::mock();
        $mainNav->shouldReceive('findOneBy->setVisible')->with(0);

        $this->sut->setMainNavigationService($mainNav);

        $event = new RouteParam();
        $event->setValue($licenceId);

        $this->sut->onLicence($event);
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockNavigation = m::mock(); // 'right-sidebar'
        $mockAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();
        $mockMarkerService = m::mock(\Olcs\Service\Marker\MarkerService::class);
        $mainNav = m::mock();

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Licence')->andReturn($mockLicenceService);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockNavigation);
        $mockSl->shouldReceive('get')->with(\Olcs\Service\Marker\MarkerService::class)->andReturn($mockMarkerService);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with('Navigation')->andReturn($mainNav);

        $sut = new Licence();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockLicenceService, $sut->getLicenceService());
        $this->assertSame($mockNavigation, $sut->getNavigationService());
        $this->assertSame($mockMarkerService, $sut->getMarkerService());
        $this->assertSame($mockAnnotationBuilder, $sut->getAnnotationBuilderService());
        $this->assertSame($mockQueryService, $sut->getQueryService());
        $this->assertSame($mainNav, $sut->getMainNavigationService());
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
