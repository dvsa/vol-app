<?php

namespace OlcsTest\Listener\RouteParam;

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
use Dvsa\Olcs\Transfer\Query\Nr\ReputeUrl as ReputeQry;
use Common\RefData;

class TransportManagerTest extends MockeryTestCase
{
    public function testAttach()
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
     * Tests onTransportManager
     * @dataProvider onTransportManagerProvider
     */
    public function testOnTransportManager($reputeUrl)
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

        $pageTitle = '<a class="govuk-link" href="'. $url . '">' . $tm['homeCd']['person']['forename'] . ' ';
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
        $mockCheckRepute->shouldReceive('setUri')->with($reputeUrl);

        $mockDetailsReview = m::mock(PageMvc::class);
        $mockDetailsReview->shouldReceive('setVisible')->with(true);

        $sidebarNav = m::mock(Navigation::class);
        $sidebarNav->shouldReceive('findById')
            ->with('transport_manager_details_review')
            ->andReturn($mockDetailsReview);
        $sidebarNav->shouldReceive('findById')
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

        $this->setupGetTransportManager($sut, $tm, $reputeUrl);

        $mockContainer = m::mock(\Laminas\View\Helper\Placeholder\Container::class);
        $mockContainer->shouldReceive('prepend')->with($pageTitle);
        $mockContainer->shouldReceive('set')->with($tm);

        $mockPlaceholder = m::mock(\Laminas\View\Helper\Placeholder::class);
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('transportManager')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->once()->with('note')->andReturn(
            m::mock()->shouldReceive('set')->once()->with($tm['latestNote']['comment'])->getMock()
        );

        $mockViewHelperManager = m::mock(\Laminas\View\HelperPluginManager::class);
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('pageTitle')->andReturn($mockContainer);
        $mockViewHelperManager->shouldReceive('get')->with('url')->andReturn($mockUrl);

        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setSidebarNavigation($sidebarNav);
        $sut->onTransportManager($event);
    }

    public function testOnTransportManagerNotMerged()
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
        $pageTitle = '<a class="govuk-link" href="'. $url . '">' . $tm['homeCd']['person']['forename'] . ' ';
        $pageTitle .= $tm['homeCd']['person']['familyName'] . '</a>';

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

        $this->setupGetTransportManager($sut, $tm);

        $mockContainer = m::mock(\Laminas\View\Helper\Placeholder\Container::class);
        $mockContainer->shouldReceive('prepend')->with($pageTitle);
        $mockContainer->shouldReceive('set')->with($tm);

        $mockPlaceholder = m::mock(\Laminas\View\Helper\Placeholder::class);
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('transportManager')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->once()->with('note')->andReturn(
            m::mock()->shouldReceive('set')->once()->with($tm['latestNote']['comment'])->getMock()
        );

        $mockViewHelperManager = m::mock(\Laminas\View\HelperPluginManager::class);
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('pageTitle')->andReturn($mockContainer);
        $mockViewHelperManager->shouldReceive('get')->with('url')->andReturn($mockUrl);

        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setSidebarNavigation($sidebarNav);

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

    public function testOnTransportManagerMerged()
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
        $pageTitle = '<a class="govuk-link" href="'. $url . '">' . $tm['homeCd']['person']['forename'] . ' ';
        $pageTitle .= $tm['homeCd']['person']['familyName'] . '</a>';

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

        $this->setupGetTransportManager($sut, $tm);

        $mockContainer = m::mock(\Laminas\View\Helper\Placeholder\Container::class);
        $mockContainer->shouldReceive('prepend')->with($pageTitle);
        $mockContainer->shouldReceive('set')->with($tm);

        $mockPlaceholder = m::mock(\Laminas\View\Helper\Placeholder::class);
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('transportManager')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->once()->with('note')->andReturn(
            m::mock()->shouldReceive('set')->once()->with($tm['latestNote']['comment'])->getMock()
        );

        $mockViewHelperManager = m::mock(\Laminas\View\HelperPluginManager::class);
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

    private function setupGetTransportManager(TransportManager $sut, array $tmData = [], $reputeUrl = null)
    {
        $mockAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();
        $mockTmResponse = m::mock();
        $mockNrResponse = m::mock();
        $mockAuthService = m::mock();
        if ($reputeUrl) {
            $mockAuthService
                ->shouldReceive('isGranted')
                ->with(RefData::PERMISSION_INTERNAL_EDIT)
                ->andReturn(true)
                ->once()
                ->getMock();
        }

        $mockAnnotationBuilder->shouldReceive('createQuery')->with(m::type(TmQry::class))->once()->andReturnUsing(
            function ($dto) {
                $this->assertInstanceOf(TmQry::class, $dto);
                $this->assertSame(1, $dto->getId());
                return 'TM QUERY';
            }
        );

        $mockAnnotationBuilder->shouldReceive('createQuery')->with(m::type(ReputeQry::class))->once()->andReturnUsing(
            function ($dto) {
                $this->assertInstanceOf(ReputeQry::class, $dto);
                $this->assertSame(1, $dto->getId());
                return 'NR QUERY';
            }
        );

        $mockQueryService->shouldReceive('send')->with('TM QUERY')->once()->andReturn($mockTmResponse);
        $mockQueryService->shouldReceive('send')->with('NR QUERY')->once()->andReturn($mockNrResponse);

        $mockTmResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockTmResponse->shouldReceive('getResult')->with()->once()->andReturn($tmData);
        $mockNrResponse->shouldReceive('isOk')->once()->andReturn(true);
        $mockNrResponse->shouldReceive('getResult')->once()->andReturn(['reputeUrl' => $reputeUrl]);

        $sut->setAnnotationBuilder($mockAnnotationBuilder);
        $sut->setQueryService($mockQueryService);
        $sut->setAuthService($mockAuthService);
    }

    /**
     * data provider for testOnTransportManager
     */
    public function onTransportManagerProvider()
    {
        return [
            ['http://www.example.com'],
            [null]
        ];
    }

    /**
     * Tests create service
     */
    public function testInvoke()
    {
        $mockAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();
        $mockAuthService= m::mock();

        $sidebarNav = m::mock(Navigation::class);
        $mockViewHelperManager = m::mock(\Laminas\View\HelperPluginManager::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($sidebarNav);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with(\LmcRbacMvc\Service\AuthorizationService::class)->andReturn($mockAuthService);

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
