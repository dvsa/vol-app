<?php

namespace Olcs\Controller\Factory\Messages;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Messages\LicenceConversationMessagesController;

class LicenceConversationMessagesControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param  $requestedName
     * @param array|null $options
     * @return LicenceConversationMessagesController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceConversationMessagesController
    {
        $formHelper = $container->get(FormHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        $scriptsFactory = $container->get(ScriptFactory::class);

        $navigation = $container->get('navigation');

        return new LicenceConversationMessagesController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation,
            $scriptsFactory
        );
    }
}
