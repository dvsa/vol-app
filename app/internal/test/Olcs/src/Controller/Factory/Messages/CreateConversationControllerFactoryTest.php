<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Factory\Messages;

use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Navigation\Navigation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Factory\Messages\CreateConversationControllerFactory;
use Olcs\Controller\Messages\LicenceCreateConversationController;
use Psr\Container\ContainerInterface;

final class CreateConversationControllerFactoryTest extends MockeryTestCase
{
    public function testInvokeInjectsTheUploadHelper(): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with(FormHelperService::class)->andReturn(m::mock(FormHelperService::class));
        $container->expects('get')->with(TranslationHelperService::class)->andReturn(m::mock(TranslationHelperService::class));
        $container->expects('get')->with(FlashMessengerHelperService::class)->andReturn(m::mock(FlashMessengerHelperService::class));
        $container->expects('get')->with('navigation')->andReturn(m::mock(Navigation::class));
        $container->expects('get')->with(FileUploadHelperService::class)->andReturn(m::mock(FileUploadHelperService::class));

        $this->assertInstanceOf(
            LicenceCreateConversationController::class,
            (new CreateConversationControllerFactory())($container, LicenceCreateConversationController::class),
        );
    }
}
