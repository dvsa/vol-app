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
        $tm['contactDetails']['person']['forename'] = 'A';
        $tm['contactDetails']['person']['familyName'] = 'B';

        $pageTitle = $tm['contactDetails']['person']['forename'] . ' ';
        $pageTitle .= $tm['contactDetails']['person']['familyName'];

        $sut = new SystemUnderTest();

        $event = new RouteParam();
        $event->setValue($tmId);

        $mockService = m::mock('Common\Service\Data\Generic');
        $mockService->shouldReceive('fetchOne')->with($tmId)->andReturn($tm);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('append')->with($pageTitle);
        $mockContainer->shouldReceive('set')->with($tm);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('transportManager')->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('pageTitle')->andReturn($mockContainer);

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
