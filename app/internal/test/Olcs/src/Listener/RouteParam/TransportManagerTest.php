<?php

declare(strict_types=1);

namespace OlcsTest\Listener\RouteParam;

use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\Placeholder\Container;
use Laminas\View\HelperPluginManager;
use LmcRbacMvc\Service\AuthorizationService;
use Psr\Container\ContainerInterface;
use Laminas\EventManager\Event;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\TransportManager\Details\TransportManagerDetailsResponsibilityController;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParams;
use Olcs\Listener\RouteParam\TransportManager;
use Mockery as m;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\Uri as PageUri;
use Laminas\Navigation\Page\Mvc as PageMvc;
use Dvsa\Olcs\Transfer\Query\Tm\TransportManager as TmQry;
use Common\RefData;

class TransportManagerTest extends MockeryTestCase
{
    public function testAttach(): void
    {
        $sut = new TransportManager();

        /** @var EventManagerInterface $eventManager */
        $eventManager = m::mock(EventManagerInterface::class);
        $eventManager->shouldReceive('attach')
            ->with(RouteParams::EVENT_PARAM . 'transportManager', [$sut, 'onTransportManager'], 1)
            ->once();

        $sut->attach($eventManager);
    }

    /**
     * @dataProvider dpInternalEditProvider
     */
    public function testOnTransportManager(bool $isInternalEdit): void
    {
        $tmId = 1;

        $context = [
            'controller' => TransportManagerDetailsResponsibilityController::class,
            'action' => 'edit-tm-application'
        ];

        $tm = ['id' => $tmId];
        $tm['homeCd']['person']['forename'] = 'A';
        $tm['homeCd']['person']['familyName'] = 'B';
        $tm['removedDate'] = 'notnull';
        $tm['hasBeenMerged'] = false;
        $tm['tmStatus']['id'] = RefData::TRANSPORT_MANAGER_STATUS_CURRENT;
        $tm['latestNote'] = [
            'comment' => 'latest note'
        ];

        $url = '#';

        $pageTitle = '<a class="govuk-link" href="' . $url . '">' . $tm['homeCd']['person']['forename'] . ' ';
        $pageTitle .= $tm['homeCd']['person']['familyName'] . '</a>';

        $mockUrl = m::mock('stdClass');
        $mockUrl->shouldReceive('__invoke')
            ->with('transport-manager/details', ['transportManager' => $tm['id']], [], true)
            ->andReturn($url);

        $sut = new TransportManager();

        $routeParam = new RouteParam();
        $routeParam->setValue($tmId);
        $routeParam->setContext($context);
        $event = new Event(null, $routeParam);

        $mockCheckRepute = m::mock(PageUri::class);
        $mockCheckRepute->shouldReceive('setVisible')->with(true)->andReturnSelf();

        $mockDetailsReview = m::mock(PageMvc::class);
        $mockDetailsReview->shouldReceive('setVisible')->with(true);

        $sidebarNav = m::mock(Navigation::class);
        $sidebarNav->shouldReceive('findById')
            ->with('transport_manager_details_review')
            ->andReturn($mockDetailsReview);
        $sidebarNav->expects('findById')
            ->times($isInternalEdit ? 1 : 0)
            ->with('transport-manager-quick-actions-check-repute')
            ->andReturn($mockCheckRepute);
        $sidebarNav->shouldReceive('findById')
            ->with('transport-manager-quick-actions-remove')
            ->andReturn(
                m::mock(PageMvc::class)
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->getMock()
            );
        $sidebarNav->shouldReceive('findById')
            ->with('transport-manager-quick-actions-merge')
            ->andReturn(
                m::mock(PageMvc::class)
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->getMock()
            );
        $sidebarNav->shouldReceive('findById')
            ->with('transport-manager-quick-actions-unmerge')
            ->andReturn(
                m::mock(PageMvc::class)
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->getMock()
            );
        $sidebarNav->shouldReceive('findById')
            ->with('transport-manager-quick-actions-undo-disqualification')
            ->andReturn(
                m::mock(PageMvc::class)
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->getMock()
            );

        $this->setupGetTransportManager($sut, $isInternalEdit, $tm);

        $mockContainer = m::mock(Container::class);
        $mockContainer->shouldReceive('prepend')->with($pageTitle);
        $mockContainer->shouldReceive('set')->with($tm);

        $mockPlaceholder = m::mock(Placeholder::class);
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('transportManager')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->once()->with('note')->andReturn(
            m::mock()->shouldReceive('set')->once()->with($tm['latestNote']['comment'])->getMock()
        );

        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('pageTitle')->andReturn($mockContainer);
        $mockViewHelperManager->shouldReceive('get')->with('url')->andReturn($mockUrl);

        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setSidebarNavigation($sidebarNav);
        $sut->onTransportManager($event);
    }

    /**
     * @dataProvider dpInternalEditProvider
     */
    public function testOnTransportManagerNotMerged(bool $isInternalEdit): void
    {
        $tmId = 1;
        $context = [
            'controller' => 'foo',
            'action' => 'bar'
        ];

        $tm = ['id' => $tmId];
        $tm['homeCd']['person']['forename'] = 'A';
        $tm['homeCd']['person']['familyName'] = 'B';
        $tm['removedDate'] = null;
        $tm['hasBeenMerged'] = false;
        $tm['tmStatus']['id'] = RefData::TRANSPORT_MANAGER_STATUS_CURRENT;
        $tm['latestNote'] = [
            'comment' => 'latest note'
        ];

        $url = '#';
        $pageTitle = '<a class="govuk-link" href="' . $url . '">' . $tm['homeCd']['person']['forename'] . ' ';
        $pageTitle .= $tm['homeCd']['person']['familyName'] . '</a>';

        $mockCheckRepute = m::mock(PageUri::class);
        $mockCheckRepute->shouldReceive('setVisible')->with(true)->andReturnSelf();

        $sut = new TransportManager();

        $mockUrl = m::mock('stdClass');
        $mockUrl->shouldReceive('__invoke')
            ->with('transport-manager/details', ['transportManager' => $tm['id']], [], true)
            ->andReturn($url);

        $sidebarNav = m::mock(Navigation::class);
        $sidebarNav->shouldReceive('findById')
            ->with('transport-manager-quick-actions-merge')
            ->andReturn(
                m::mock(PageMvc::class)
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->getMock()
            );

        $this->setupGetTransportManager($sut, $isInternalEdit, $tm);

        $mockContainer = m::mock(Container::class);
        $mockContainer->shouldReceive('prepend')->with($pageTitle);
        $mockContainer->shouldReceive('set')->with($tm);

        $mockPlaceholder = m::mock(Placeholder::class);
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('transportManager')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->once()->with('note')->andReturn(
            m::mock()->shouldReceive('set')->once()->with($tm['latestNote']['comment'])->getMock()
        );

        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('pageTitle')->andReturn($mockContainer);
        $mockViewHelperManager->shouldReceive('get')->with('url')->andReturn($mockUrl);

        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setSidebarNavigation($sidebarNav);

        $sidebarNav->expects('findById')
            ->times($isInternalEdit ? 1 : 0)
            ->with('transport-manager-quick-actions-check-repute')
            ->andReturn($mockCheckRepute);

        $sidebarNav->shouldReceive('findById')
            ->with('transport-manager-quick-actions-unmerge')
            ->andReturn(
                m::mock(PageMvc::class)
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->getMock()
            );
        $sidebarNav->shouldReceive('findById')
            ->with('transport-manager-quick-actions-undo-disqualification')
            ->andReturn(
                m::mock(PageMvc::class)
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->getMock()
            );

        $routeParam = new RouteParam();
        $routeParam->setValue($tmId);
        $routeParam->setContext($context);
        $event = new Event(null, $routeParam);

        $sut->onTransportManager($event);
    }


    /**
     * @dataProvider dpInternalEditProvider
     */
    public function testOnTransportManagerMerged(bool $isInternalEdit): void
    {
        $tmId = 1;
        $context = [
            'controller' => 'foo',
            'action' => 'bar'
        ];

        $tm = ['id' => $tmId];
        $tm['homeCd']['person']['forename'] = 'A';
        $tm['homeCd']['person']['familyName'] = 'B';
        $tm['removedDate'] = null;
        $tm['hasBeenMerged'] = true;
        $tm['tmStatus']['id'] = RefData::TRANSPORT_MANAGER_STATUS_DISQUALIFIED;
        $tm['latestNote'] = [
            'comment' => 'latest note'
        ];

        $url = '#';
        $pageTitle = '<a class="govuk-link" href="' . $url . '">' . $tm['homeCd']['person']['forename'] . ' ';
        $pageTitle .= $tm['homeCd']['person']['familyName'] . '</a>';

        $mockCheckRepute = m::mock(PageUri::class);
        $mockCheckRepute->shouldReceive('setVisible')->with(true)->andReturnSelf();

        $sut = new TransportManager();

        $mockUrl = m::mock('stdClass');
        $mockUrl->shouldReceive('__invoke')
            ->with('transport-manager/details', ['transportManager' => $tm['id']], [], true)
            ->andReturn($url);

        $sidebarNav = m::mock(Navigation::class);
        $sidebarNav->shouldReceive('findById')
            ->with('transport-manager-quick-actions-merge')
            ->andReturn(
                m::mock(PageMvc::class)
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->getMock()
            );

        $sidebarNav->expects('findById')
            ->times($isInternalEdit ? 1 : 0)
            ->with('transport-manager-quick-actions-check-repute')
            ->andReturn($mockCheckRepute);

        $this->setupGetTransportManager($sut, $isInternalEdit, $tm);

        $mockContainer = m::mock(Container::class);
        $mockContainer->shouldReceive('prepend')->with($pageTitle);
        $mockContainer->shouldReceive('set')->with($tm);

        $mockPlaceholder = m::mock(Placeholder::class);
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('transportManager')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->once()->with('note')->andReturn(
            m::mock()->shouldReceive('set')->once()->with($tm['latestNote']['comment'])->getMock()
        );

        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('pageTitle')->andReturn($mockContainer);
        $mockViewHelperManager->shouldReceive('get')->with('url')->andReturn($mockUrl);

        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setSidebarNavigation($sidebarNav);

        $sidebarNav->shouldReceive('findById')
            ->with('transport-manager-quick-actions-merge')
            ->andReturn(
                m::mock(PageMvc::class)
                    ->shouldReceive('setVisible')
                    ->with(false)
                    ->getMock()
            );

        $routeParam = new RouteParam();
        $routeParam->setValue($tmId);
        $routeParam->setContext($context);
        $event = new Event(null, $routeParam);

        $sut->onTransportManager($event);
    }

    private function setupGetTransportManager(TransportManager $sut, bool $isInternalEdit, array $tmData): void
    {
        $mockAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();
        $mockTmResponse = m::mock();
        $mockAuthService = m::mock(AuthorizationService::class);

        $mockAuthService
            ->expects('isGranted')
            ->with(RefData::PERMISSION_INTERNAL_EDIT)
            ->andReturn($isInternalEdit)
            ->getMock();

        $mockAnnotationBuilder->shouldReceive('createQuery')->with(m::type(TmQry::class))->once()->andReturnUsing(
            function ($dto) {
                $this->assertInstanceOf(TmQry::class, $dto);
                $this->assertSame(1, $dto->getId());
                return 'TM QUERY';
            }
        );

        $mockQueryService->shouldReceive('send')->with('TM QUERY')->once()->andReturn($mockTmResponse);

        $mockTmResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockTmResponse->shouldReceive('getResult')->with()->once()->andReturn($tmData);

        $sut->setAnnotationBuilder($mockAnnotationBuilder);
        $sut->setQueryService($mockQueryService);
        $sut->setAuthService($mockAuthService);
    }

    public function dpInternalEditProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testInvoke(): void
    {
        $mockAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();
        $mockAuthService = m::mock();

        $sidebarNav = m::mock(Navigation::class);
        $mockViewHelperManager = m::mock(HelperPluginManager::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($sidebarNav);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with(AuthorizationService::class)->andReturn($mockAuthService);

        $sut = new TransportManager();
        $service = $sut->__invoke($mockSl, TransportManager::class);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($sidebarNav, $sut->getSidebarNavigation());
        $this->assertSame($mockAnnotationBuilder, $sut->getAnnotationBuilder());
        $this->assertSame($mockQueryService, $sut->getQueryService());
        $this->assertSame($mockAuthService, $sut->getAuthService());
    }
}
