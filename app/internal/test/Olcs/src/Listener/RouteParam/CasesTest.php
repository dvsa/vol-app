<?php

namespace OlcsTest\Listener\RouteParam;

use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Olcs\Listener\RouteParam\Cases;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Class CasesTest
 * @package OlcsTest\Listener\RouteParam
 */
class CasesTest extends MockeryTestCase
{
    public function setUp()
    {
        $this->sut = new Cases();

        parent::setUp();
    }

    public function setupMockCase($id, $data)
    {
        $mockAnnotationBuilder = m::mock();
        $mockQueryService  = m::mock();

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($id) {
                $this->assertSame($id, $dto->getId());
                return 'QUERY';
            }
        );

        $mockResult = m::mock();
        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResult->shouldReceive('getResult')->with()->once()->andReturn($data);

        $mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResult);

        $this->sut->setAnnotationBuilder($mockAnnotationBuilder);
        $this->sut->setQueryService($mockQueryService);
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'case', [$this->sut, 'onCase'], 1);

        $this->sut->attach($mockEventManager);
    }

    public function testOnCase()
    {
        $id = 69;
        $case = [
            'id' => $id,
            'application' => [
                'id' => 100,
            ],
            'licence' => [
                'id' => 101,
            ],
            'transportManager' => [
                'id' => 102,
            ],
            'tmDecisions' => [
                ['id' => 200]
            ]
        ];

        $event = new RouteParam();
        $event->setValue($id);
        $event->setTarget(
            m::mock()
            ->shouldReceive('trigger')->once()->with('application', 100)
            ->shouldReceive('trigger')->once()->with('licence', 101)
            ->shouldReceive('trigger')->once()->with('transportManager', 102)
            ->getMock()
        );

        $this->setupMockCase($id, $case);

        $mockPlaceholder = m::mock()
            ->shouldReceive('getContainer')->once()->with('case')->andReturn(
                m::mock()->shouldReceive('set')->once()->with($case)->getMock()
            )
            ->getMock();

        $mockViewHelperManager = m::mock('\Zend\View\HelperPluginManager')
            ->shouldReceive('get')->once()->with('placeholder')->andReturn($mockPlaceholder)
            ->getMock();

        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockNavigation = m::mock()
            ->shouldReceive('findOneById')->once()->with('case_opposition')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_details_legacy_offence')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_details_annual_test_history')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_details_prohibitions')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_details_statements')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_details_conditions_undertakings')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_details_impounding')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case_processing_in_office_revocation')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->getMock();

        $this->sut->setNavigationService($mockNavigation);

        $mockSidebar = m::mock()
            ->shouldReceive('findOneById')->once()->with('case-decisions-transport-manager-repute-not-lost')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case-decisions-transport-manager-declare-unfit')->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->shouldReceive('findOneById')->once()->with('case-decisions-transport-manager-no-further-action')
            ->andReturn(
                m::mock()->shouldReceive('setVisible')->once()->with(false)->getMock()
            )
            ->getMock();

        $this->sut->setSidebarNavigationService($mockSidebar);

        $this->sut->onCase($event);
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockNavigation = m::mock();
        $mockSidebar = m::mock();
        $mockTransferAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('Navigation')->andReturn($mockNavigation);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($mockSidebar);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockTransferAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);

        $service = $this->sut->createService($mockSl);

        $this->assertSame($this->sut, $service);
        $this->assertSame($mockViewHelperManager, $this->sut->getViewHelperManager());
        $this->assertSame($mockTransferAnnotationBuilder, $this->sut->getAnnotationBuilder());
        $this->assertSame($mockQueryService, $this->sut->getQueryService());
        $this->assertSame($mockNavigation, $this->sut->getNavigationService());
        $this->assertSame($mockSidebar, $this->sut->getSidebarNavigationService());
    }

    /**
     * @expectedException \Common\Exception\ResourceNotFoundException
     */
    public function testOnCaseNotFound()
    {
        $id = 69;

        $event = new RouteParam();
        $event->setValue($id);

        $mockAnnotationBuilder = m::mock();
        $mockQueryService  = m::mock();

        $mockAnnotationBuilder->shouldReceive('createQuery')->once()->andReturnUsing(
            function ($dto) use ($id) {
                $this->assertSame($id, $dto->getId());
                return 'QUERY';
            }
        );

        $mockResult = m::mock();
        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);

        $mockQueryService->shouldReceive('send')->with('QUERY')->once()->andReturn($mockResult);

        $this->sut->setAnnotationBuilder($mockAnnotationBuilder);
        $this->sut->setQueryService($mockQueryService);

        $this->sut->onCase($event);
    }
}
