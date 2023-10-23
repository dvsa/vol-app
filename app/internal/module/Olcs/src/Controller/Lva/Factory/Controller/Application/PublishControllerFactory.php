<?php

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Application\PublishController;
use ZfcRbac\Service\AuthorizationService;

class PublishControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return PublishController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PublishController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new PublishController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $stringHelper,
            $restrictionHelper,
            $flashMessengerHelper,
            $navigation
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PublishController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): PublishController
    {
        return $this->__invoke($serviceLocator, PublishController::class);
    }
}
