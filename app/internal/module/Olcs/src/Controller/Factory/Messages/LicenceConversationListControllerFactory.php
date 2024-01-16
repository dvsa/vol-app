<?php

namespace Olcs\Controller\Factory\Messages;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Navigation\Navigation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Messages\LicenceConversationListController;

class LicenceConversationListControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return LicenceConversationListController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceConversationListController
    {
        $formHelper = $container->get(FormHelperService::class);
        assert($formHelper instanceof FormHelperService);

        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('navigation');
        assert($navigation instanceof Navigation);

        return new LicenceConversationListController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation
        );
    }
}
