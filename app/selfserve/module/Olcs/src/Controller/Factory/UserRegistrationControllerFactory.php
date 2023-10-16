<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\UserRegistrationController;
use ZfcRbac\Service\AuthorizationService;

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
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

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

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return UserRegistrationController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): UserRegistrationController
    {
        return $this->__invoke($serviceLocator, UserRegistrationController::class);
    }
}
