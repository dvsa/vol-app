<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\UserRegistrationController;
use LmcRbacMvc\Service\AuthorizationService;

class UserRegistrationControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return UserRegistrationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UserRegistrationController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $urlHelper = $container->get(UrlHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new UserRegistrationController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $scriptFactory,
            $translationHelper,
            $urlHelper,
            $flashMessengerHelper
        );
    }
}
