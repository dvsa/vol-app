<?php

declare(strict_types=1);

namespace AdminTest\Controller;

use Admin\Controller\LongTextController;
use Admin\Controller\LongTextControllerFactory;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Navigation\Navigation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

final class LongTextControllerFactoryTest extends MockeryTestCase
{
    public function testInvokeCreatesLongTextController(): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(TranslationHelperService::class)
            ->andReturn(m::mock(TranslationHelperService::class));
        $container->shouldReceive('get')->with(FormHelperService::class)
            ->andReturn(m::mock(FormHelperService::class));
        $container->shouldReceive('get')->with(FlashMessengerHelperService::class)
            ->andReturn(m::mock(FlashMessengerHelperService::class));
        $container->shouldReceive('get')->with('navigation')
            ->andReturn(m::mock(Navigation::class));

        $controller = (new LongTextControllerFactory())($container, LongTextController::class);

        self::assertInstanceOf(LongTextController::class, $controller);
    }
}
