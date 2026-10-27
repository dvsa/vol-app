<?php

declare(strict_types=1);

namespace CommonTest\View\Factory\Helper;

use Common\Service\FlashMessenger\LaminasSessionFlashMessenger;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\View\Factory\Helper\FlashMessengerFactory;
use Common\View\Helper\FlashMessenger;
use Laminas\I18n\Translator\Translator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

final class FlashMessengerFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $flashMessenger = m::mock(LaminasSessionFlashMessenger::class);
        $flashMessengerHelperService = m::mock(FlashMessengerHelperService::class);
        $translator = m::mock(Translator::class);

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with(LaminasSessionFlashMessenger::class)->andReturn($flashMessenger);
        $container->expects('get')->with(FlashMessengerHelperService::class)->andReturn($flashMessengerHelperService);
        $container->expects('get')->with('translator')->andReturn($translator);

        $sut = new FlashMessengerFactory();
        $this->assertInstanceOf(FlashMessenger::class, $sut->__invoke($container, FlashMessenger::class));
    }
}
