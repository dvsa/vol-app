<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as MockeryTestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\BusRegId as SystemUnderTest;
use Mockery as m;
use Olcs\Listener\RouteParams;

/**
 * Class ActionTest
 * @package OlcsTest\Listener\RouteParam
 */
class BusRegIdTest extends MockeryTestCase
{
    public function testOnBusRegId()
    {
        $busRegId = 1;
        $busReg = [];
        $busReg['id'] = $busRegId;
        $busReg['status']['id'] = 'breg_s_admin';
        $busReg['status']['description'] = 'Admin';
        $busReg['isShortNotice'] = 'Y';
        $busReg['licence']['organisation']['name'] = 'Org Name';
        $busReg['variationNo'] = '1';
        $busReg['routeNo'] = 'YO!@';
        $busReg['regNo'] = 'ABC';
        $busReg['licence']['id'] = '1234';
        $busReg['licence']['licNo'] = 'HELLO1234';

        $sut = new SystemUnderTest();

        $mockLicenceService = m::mock('Olcs\Service\Data\Licence');
        $mockLicenceService->shouldReceive('setData')->with($busReg['licence']['id'],$busReg['licence']);

        $urlHelper = $mockPlaceholder = m::mock('Zend\View\Helper\Url');
        $urlHelper->shouldReceive('__invoke')->andReturn('NOTHING');
        // 1 time
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('Url')->andReturn($urlHelper);
        $sut->setViewHelperManager($mockViewHelperManager);

        $statusArray = $sut->getStatusArray($busReg['status']['id'], $busReg['status']['description']);
        $pageTitle = $sut->getPageTitle($busReg);
        $subTitle = $sut->getSubTitle($busReg);

        $mockTarget = m::mock('Olcs\Listener\RouteParams');
        $mockTarget->shouldReceive('trigger')->with('licence', $busReg['licence']['id']);

        $event = new RouteParam();
        $event->setValue($busRegId);
        $event->setTarget($mockTarget);

        $mockHeadTitleHelper = m::mock('Zend\View\Helper\HeadTitle');
        $mockHeadTitleHelper->shouldReceive('prepend')->with($busReg['regNo']);

        $mockService = m::mock('Common\Service\Data\Generic');
        $mockService->shouldReceive('fetchOne')->with($busRegId)->andReturn($busReg);

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');

        $mockContainer->shouldReceive('set')->once()->with($statusArray);
        $mockContainer->shouldReceive('set')->once()->with($busReg);
        $mockContainer->shouldReceive('append')->once()->with($pageTitle);
        $mockContainer->shouldReceive('append')->once()->with($subTitle);

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('status')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('pageSubtitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('headTitle')->andReturn($mockContainer);
        $mockPlaceholder->shouldReceive('getContainer')->with('busReg')->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);
        $mockViewHelperManager->shouldReceive('get')->with('headTitle')->andReturn($mockHeadTitleHelper);
        // 2 time
        $mockViewHelperManager->shouldReceive('get')->with('Url')->andReturn($urlHelper);

        $sut->setLicenceService($mockLicenceService);
        $sut->setBusRegService($mockService);
        $sut->setViewHelperManager($mockViewHelperManager);
        $sut->onBusRegId($event);
    }

    public function testCreateService()
    {
        $mockService = m::mock('Common\Service\Data\Generic');
        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');

        $mockDataSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockDataSl->shouldReceive('get')->with('Generic\Service\Data\BusReg')
                   ->andReturn($mockService);
        $mockDataSl->shouldReceive('get')->with('Common\Service\Data\Licence')
            ->andReturn($mockLicenceService);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturn($mockDataSl);

        $sut = new SystemUnderTest();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
    }
}
