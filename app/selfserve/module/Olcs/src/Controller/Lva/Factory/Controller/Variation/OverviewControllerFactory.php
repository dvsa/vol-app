<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Variation\OverviewController;
use Olcs\Service\Helper\ApplicationOverviewHelperService;
use ZfcRbac\Service\AuthorizationService;

class OverviewControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return OverviewController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OverviewController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $formHelper = $container->get(FormHelperService::class);

        return new OverviewController(
            $niTextTranslationUtil,
            $authService,
            $formServiceManager,
            $formHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OverviewController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): OverviewController
    {
        return $this->__invoke($serviceLocator, OverviewController::class);
    }
}
