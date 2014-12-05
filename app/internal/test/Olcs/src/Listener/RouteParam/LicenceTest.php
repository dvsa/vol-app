<?php

namespace OlcsTest\Listener\RouteParam;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Event\RouteParam;
use Olcs\Listener\RouteParam\Licence;
use Mockery as m;
use Olcs\Listener\RouteParams;

/**
 * Class LicenceTest
 * @package OlcsTest\Listener\RouteParam
 */
class LicenceTest extends TestCase
{
    public function testAttach()
    {
        $sut = new Licence();

        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(RouteParams::EVENT_PARAM . 'licence', [$sut, 'onLicence'], 1);

        $sut->attach($mockEventManager);
    }

    public function testOnLicence()
    {
        $licenceId = 4;
        $licence = [
            'id' => $licenceId,
            'licNo' => 'L2347137'
        ];

        $event = new RouteParam();
        $event->setValue($licenceId);

        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockLicenceService->shouldReceive('fetchLicenceData')->with($licenceId)->andReturn($licence);
        $mockLicenceService->shouldReceive('setId')->with($licenceId);

        $mockRouter = m::mock('Zend\Mvc\Router\RouteStackInterface');
        $mockRouter->shouldReceive('assemble')
            ->with(['licence' => $licenceId], ['name' => 'licence/cases'])
            ->andReturn('http://licence-url/');

        $mockContainer = m::mock('Zend\View\Helper\Placeholder\Container');
        $mockContainer->shouldReceive('prepend')->with('<a href="http://licence-url/">L2347137</a>');

        $mockPlaceholder = m::mock('Zend\View\Helper\Placeholder');
        $mockPlaceholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($mockContainer);

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($mockPlaceholder);

        $sut = new Licence();
        $sut->setLicenceService($mockLicenceService);
        $sut->setRouter($mockRouter);
        $sut->setViewHelperManager($mockViewHelperManager);

        $sut->onLicence($event);
    }

    public function testCreateService()
    {
        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockRouter = m::mock('Zend\Mvc\Router\RouteStackInterface');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Common\Service\Data\Licence')->andReturn($mockLicenceService);
        $mockSl->shouldReceive('get')->with('Router')->andReturn($mockRouter);

        $sut = new Licence();
        $service = $sut->createService($mockSl);

        $this->assertSame($sut, $service);
        $this->assertSame($mockRouter, $sut->getRouter());
        $this->assertSame($mockLicenceService, $sut->getLicenceService());
        $this->assertSame($mockViewHelperManager, $sut->getViewHelperManager());
    }
}
