<?php

declare(strict_types=1);

namespace CommonTest\View\Factory\Helper;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\View\Factory\Helper\FlashMessengerFactory;
use Common\View\Helper\FlashMessenger;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger as LaminasFlashMessengerPlugin;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

class FlashMessengerFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $flashMessenger = m::mock(LaminasFlashMessengerPlugin::class);
        $flashMessengerHelperService = m::mock(FlashMessengerHelperService::class);

        $controllerPluginManager = m::mock(PluginManager::class);
        $controllerPluginManager->expects('get')->with('FlashMessenger')->andReturn($flashMessenger);

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('ControllerPluginManager')->andReturn($controllerPluginManager);
        $container->expects('get')->with('Helper\FlashMessenger')->andReturn($flashMessengerHelperService);

        $sut = new FlashMessengerFactory();
        $this->assertInstanceOf(FlashMessenger::class, $sut->__invoke($container, FlashMessenger::class));
    }
}
