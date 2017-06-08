<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\TransportManager\Details\TransportManagerDetailsResponsibilityController;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\TransportManager as SystemUnderTest;
use Mockery as m;
use Zend\Navigation\Navigation;
use Zend\Navigation\Page\Uri as PageUri;
use Zend\Navigation\Page\Mvc as PageMvc;
use Dvsa\Olcs\Transfer\Query\Tm\TransportManager as TmQry;
use Dvsa\Olcs\Transfer\Query\Nr\ReputeUrl as ReputeQry;
use Common\RefData;

/**
 * Class ActionTest
 * @package OlcsTest\Listener\RouteParam
 */
class TransportManagerTest extends MockeryTestCase
{
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

        $pageTitle = '<a href="'. $url . '">' . $tm['homeCd']['person']['forename'] . ' ';
        $pageTitle .= $tm['homeCd']['person']['familyName'] . '</a>';

        $mockUrl = m::mock('stdClass');
        $mockUrl->shouldReceive('__invoke')
            ->with('transport-manager/details', ['transportManager' => $tm['id']], [], true)
            ->andReturn($url);

        $sut = new SystemUnderTest();

        $event = new RouteParam();
        $event->setValue($tmId);
        $event->setContext($context);

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

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('prepend')->with($pageTitle);
        $mockContainer->shouldReceive('set')->with($tm);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('transportManager')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->once()->with('note')->andReturn(
            m::mock()->shouldReceive('set')->once()->with($tm['latestNote']['comment'])->getMock()
        );

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
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
        $pageTitle = '<a href="'. $url . '">' . $tm['homeCd']['person']['forename'] . ' ';
        $pageTitle .= $tm['homeCd']['person']['familyName'] . '</a>';

        $sut = new SystemUnderTest();

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

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('prepend')->with($pageTitle);
        $mockContainer->shouldReceive('set')->with($tm);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('transportManager')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->once()->with('note')->andReturn(
            m::mock()->shouldReceive('set')->once()->with($tm['latestNote']['comment'])->getMock()
        );

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
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

        $event = new RouteParam();
        $event->setValue($tmId);
        $event->setContext($context);
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
        $pageTitle = '<a href="'. $url . '">' . $tm['homeCd']['person']['forename'] . ' ';
        $pageTitle .= $tm['homeCd']['person']['familyName'] . '</a>';

        $sut = new SystemUnderTest();

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

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('prepend')->with($pageTitle);
        $mockContainer->shouldReceive('set')->with($tm);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('transportManager')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->once()->with('note')->andReturn(
            m::mock()->shouldReceive('set')->once()->with($tm['latestNote']['comment'])->getMock()
        );

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
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

        $event = new RouteParam();
        $event->setValue($tmId);
        $event->setContext($context);
        $sut->onTransportManager($event);
    }

    private function setupGetTransportManager(SystemUnderTest $sut, array $tmData = [], $reputeUrl = null)
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
    public function testCreateService()
    {
        $mockAnnotationBuilder = m::mock();
        $mockQueryService = m::mock();
        $mockAuthService= m::mock();

        $sidebarNav = m::mock(Navigation::class);
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($sidebarNav);
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn($mockAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('QueryService')->andReturn($mockQueryService);
        $mockSl->shouldReceive('get')->with('ZfcRbac\Service\AuthorizationService')->andReturn($mockAuthService);

        $sut = new SystemUnderTest();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($sidebarNav, $sut->getSidebarNavigation());
        $this->assertSame($mockAnnotationBuilder, $sut->getAnnotationBuilder());
        $this->assertSame($mockQueryService, $sut->getQueryService());
        $this->assertSame($mockAuthService, $sut->getAuthService());
    }
}
