<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Variation\SummaryController;
use ZfcRbac\Service\AuthorizationService;

class SummaryControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SummaryController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SummaryController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);

        return new SummaryController(
            $niTextTranslationUtil,
            $authService
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SummaryController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): SummaryController
    {
        return $this->__invoke($serviceLocator, SummaryController::class);
    }
}
