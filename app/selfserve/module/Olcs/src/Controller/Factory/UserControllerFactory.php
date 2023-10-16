<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\DashboardController;
use Olcs\Controller\UserController;
use Olcs\View\Model\User;
use ZfcRbac\Service\AuthorizationService;

class UserControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DashboardController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UserController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $user = $container->get(User::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $guidanceHelper = $container->get(GuidanceHelperService::class);

        return new UserController(
            $niTextTranslationUtil,
            $authService,
            $user,
            $scriptFactory,
            $formHelper,
            $flashMessengerHelper,
            $translationHelper,
            $guidanceHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return UserController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): UserController
    {
        return $this->__invoke($serviceLocator, UserController::class);
    }
}
