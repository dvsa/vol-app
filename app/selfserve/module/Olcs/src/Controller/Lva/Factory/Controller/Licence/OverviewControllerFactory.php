<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Licence\OverviewController;
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
        $lvaAdapter = $container->get(LicenceLvaAdapter::class);

        return new OverviewController(
            $niTextTranslationUtil,
            $authService,
            $lvaAdapter
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
