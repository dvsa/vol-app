<?php

namespace Olcs\Controller\Factory;

use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\IndexController;
use ZfcRbac\Service\AuthorizationService;

class IndexControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IndexController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);

        return new IndexController(
            $niTextTranslationUtil,
            $authService
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return IndexController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): IndexController
    {
        return $this->__invoke($serviceLocator, IndexController::class);
    }
}
