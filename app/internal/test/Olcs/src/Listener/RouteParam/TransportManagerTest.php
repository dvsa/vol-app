<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as MockeryTestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\TransportManager as SystemUnderTest;
use Mockery as m;
use Olcs\Service\Nr\RestHelper as NrRestHelper;
use Zend\Navigation\Navigation;
use Zend\Navigation\Page\Uri as PageUri;
use Zend\Navigation\Page\Mvc as PageMvc;
use Zend\Http\Response;
use Olcs\Listener\RouteParams;
use Zend\Json\Json;

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
            'controller' => 'TMDetailsResponsibilityController',
            'action' => 'edit-tm-application'
        ];

        $tm = ['id' => $tmId];
        $tm['homeCd']['person']['forename'] = 'A';
        $tm['homeCd']['person']['familyName'] = 'B';

        $url = '#';

        $pageTitle = '<a href="'. $url . '">' . $tm['homeCd']['person']['forename'] . ' ';
        $pageTitle .= $tm['homeCd']['person']['familyName'] . '</a>';

        $mockUrl = m::mock('stdClass');
        $mockUrl->shouldReceive('__invoke')
            ->with('transport-manager/details/details', ['transportManager' => $tm['id']], [], true)
            ->andReturn($url);

        $sut = new SystemUnderTest();

        $event = new RouteParam();
        $event->setValue($tmId);
        $event->setContext($context);

        $mockNr = m::mock(NrRestHelper::class);
        $mockNr->shouldReceive('fetchTmReputeUrl')->with($tmId)->andReturn($reputeUrl);

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

        $mockService = m::mock('Common\Service\Data\Generic');
        $mockService->shouldReceive('fetchOne')->with($tmId)->andReturn($tm);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('prepend')->with($pageTitle);
        $mockContainer->shouldReceive('set')->with($tm);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('transportManager')->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('pageTitle')->andReturn($mockContainer);
        $mockViewHelperManager->shouldReceive('get')->with('url')->andReturn($mockUrl);

        $sut->setGenericService($mockService);
        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->setNrService($mockNr);
        $sut->setSidebarNavigation($sidebarNav);
        $sut->onTransportManager($event);
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
        $mockService = m::mock('Common\Service\Data\Generic');
        $mockNr = m::mock(NrRestHelper::class);
        $sidebarNav = m::mock(Navigation::class);
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');

        $mockDataSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockDataSl->shouldReceive('get')->with('Generic\Service\Data\TransportManager')
                   ->andReturn($mockService);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturn($mockDataSl);
        $mockSl->shouldReceive('get')->with('right-sidebar')->andReturn($sidebarNav);
        $mockSl->shouldReceive('get')->with(NrRestHelper::class)->andReturn($mockNr);

        $sut = new SystemUnderTest();
        $service = $sut->createService($mockSl);
        $sut->setNrService($mockNr);
        $sut->setSidebarNavigation($sidebarNav);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
        $this->assertSame($mockNr, $sut->getNrService());
        $this->assertSame($sidebarNav, $sut->getSidebarNavigation());
    }
}
