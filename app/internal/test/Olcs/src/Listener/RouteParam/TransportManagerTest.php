<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as MockeryTestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\TransportManager as SystemUnderTest;
use Mockery as m;
use Olcs\Listener\RouteParams;

/**
 * Class ActionTest
 * @package OlcsTest\Listener\RouteParam
 */
class TransportManagerTest extends MockeryTestCase
{
    public function testOnTransportManager()
    {
        $tmId = 1;
        $tm = ['id' => $tmId];
        $tm['homeCd']['person']['forename'] = 'A';
        $tm['homeCd']['person']['familyName'] = 'B';

        $url = '#';

        $pageTitle = '<a href="'. $url . '">' . $tm['homeCd']['person']['forename'] . ' ';
        $pageTitle .= $tm['homeCd']['person']['familyName'] . '</a>';

        $mockUrl = m::mock('stdClass');
        $mockUrl->shouldReceive('__invoke')->with('transport-manager/details', [], [], true)->andReturn($url);

        $sut = new SystemUnderTest();

        $event = new RouteParam();
        $event->setValue($tmId);

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
        $sut->onTransportManager($event);
    }

    public function testCreateService()
    {
        $mockService = m::mock('Common\Service\Data\Generic');

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');

        $mockDataSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockDataSl->shouldReceive('get')->with('Generic\Service\Data\TransportManager')
                   ->andReturn($mockService);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturn($mockDataSl);

        $sut = new SystemUnderTest();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
    }
}
