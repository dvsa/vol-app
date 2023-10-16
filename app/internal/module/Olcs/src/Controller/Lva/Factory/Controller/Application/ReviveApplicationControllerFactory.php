<?php

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Application\ReviveApplicationController;
use ZfcRbac\Service\AuthorizationService;

class ReviveApplicationControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return ReviveApplicationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ReviveApplicationController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);

        return new ReviveApplicationController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $translationHelper,
            $formHelper,
            $stringHelper,
            $restrictionHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ReviveApplicationController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ReviveApplicationController
    {
        return $this->__invoke($serviceLocator, ReviveApplicationController::class);
    }
}
