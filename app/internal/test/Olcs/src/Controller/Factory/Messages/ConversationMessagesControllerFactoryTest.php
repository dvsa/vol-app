<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Factory\Messages;

use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Laminas\Navigation\Navigation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Factory\Messages\ConversationMessagesControllerFactory;
use Olcs\Controller\Messages\LicenceConversationMessagesController;
use Psr\Container\ContainerInterface;

final class ConversationMessagesControllerFactoryTest extends MockeryTestCase
{
    public function testInvokeInjectsTheUploadHelper(): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with(FormHelperService::class)->andReturn(m::mock(FormHelperService::class));
        $container->expects('get')->with(TranslationHelperService::class)->andReturn(m::mock(TranslationHelperService::class));
        $container->expects('get')->with(FlashMessengerHelperService::class)->andReturn(m::mock(FlashMessengerHelperService::class));
        $container->expects('get')->with(ScriptFactory::class)->andReturn(m::mock(ScriptFactory::class));
        $container->expects('get')->with('navigation')->andReturn(m::mock(Navigation::class));
        $container->expects('get')->with(FileUploadHelperService::class)->andReturn(m::mock(FileUploadHelperService::class));

        $this->assertInstanceOf(
            LicenceConversationMessagesController::class,
            (new ConversationMessagesControllerFactory())($container, LicenceConversationMessagesController::class),
        );
    }
}
