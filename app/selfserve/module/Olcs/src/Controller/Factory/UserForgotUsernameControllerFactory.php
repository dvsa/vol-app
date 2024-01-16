<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\UserForgotUsernameController;
use LmcRbacMvc\Service\AuthorizationService;

class UserForgotUsernameControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return UserForgotUsernameController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UserForgotUsernameController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new UserForgotUsernameController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper
        );
    }
}
