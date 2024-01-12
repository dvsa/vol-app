<?php

namespace Olcs\Controller\Factory\Messages;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Navigation\Navigation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager;
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
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;
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

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return LicenceConversationMessagesController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): LicenceConversationMessagesController
    {
        return $this->__invoke($serviceLocator, LicenceConversationMessagesController::class);
    }
}
