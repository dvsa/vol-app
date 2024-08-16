<?php

declare(strict_types=1);

namespace Olcs\Controller\Factory\Messages;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Messages\AbstractConversationMessagesController;
use Psr\Container\ContainerInterface;

class ConversationMessagesControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $formHelper = $container->get(FormHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        $scriptsFactory = $container->get(ScriptFactory::class);

        $navigation = $container->get('navigation');

        return new $requestedName(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation,
            $scriptsFactory
        );
    }
}
