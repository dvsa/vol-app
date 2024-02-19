<?php

declare(strict_types=1);

namespace Olcs\Controller\Factory\Messages;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Messages\LicenceCreateConversationController;

class LicenceCreateConversationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceCreateConversationController
    {
        $formHelper = $container->get(FormHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        $navigation = $container->get('navigation');

        return new LicenceCreateConversationController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation
        );
    }
}
