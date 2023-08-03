<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\UserForgotUsernameController;
use ZfcRbac\Service\AuthorizationService;

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
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

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

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return UserForgotUsernameController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): UserForgotUsernameController
    {
        return $this->__invoke($serviceLocator, UserForgotUsernameController::class);
    }
}
