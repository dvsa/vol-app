<?php

namespace Olcs\Controller\Factory\Messages;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Messages\LicenceCreateConversationController;
use Psr\Container\NotFoundExceptionInterface;

class LicenceCreateConversationControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param                    $requestedName
     * @param array|null         $options
     * @return LicenceCreateConversationController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
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
