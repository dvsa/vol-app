<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Dispatcher;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Interop\Container\ContainerInterface;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Session\LicenceVehicleManagement;

/**
 * @see SwitchBoardController
 */
class SwitchBoardControllerFactory implements FactoryInterface
{
    /**
     * @deprecated See https://docs.laminas.dev/laminas-servicemanager/migration/#factories
     * @param ServiceLocatorInterface $serviceLocator
     * @return Dispatcher
     */
    public function createService(ServiceLocatorInterface $serviceLocator): Dispatcher
    {
        return $this($serviceLocator, SwitchBoardController::class);
    }

    /**
     * @param ContainerInterface $container
     * @param mixed $requestedName
     * @param array|null $options
     * @return Dispatcher
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Dispatcher
    {
        if ($container instanceof ServiceLocatorAwareInterface) {
            $container = $container->getServiceLocator();
        }
        $controllerPluginManager = $container->get('ControllerPluginManager');

        $controller = new SwitchBoardController(
            $controllerPluginManager->get(FlashMessenger::class),
            $container->get(FormHelperService::class),
            $controllerPluginManager->get(HandleQuery::class),
            $redirectHelper = $controllerPluginManager->get(Redirect::class),
            $container->get(ResponseHelperService::class),
            $container->get(LicenceVehicleManagement::class),
            $urlHelper = $controllerPluginManager->get(Url::class)
        );

        // Decorate controller
        $instance = new Dispatcher($controller);

        // Initialize plugins
        $urlHelper->setController($instance);
        $redirectHelper->setController($instance);

        return $instance;
    }
}
