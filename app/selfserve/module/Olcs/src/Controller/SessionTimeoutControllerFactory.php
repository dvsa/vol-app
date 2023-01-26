<?php
declare(strict_types=1);

namespace Olcs\Controller;

use Interop\Container\ContainerInterface;
use Common\Controller\Dispatcher;
use Common\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\ControllerManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Identity\IdentityProviderInterface;

/**
 * @See SessionTimeoutController
 */
class SessionTimeoutControllerFactory implements FactoryInterface
{

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return Dispatcher
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : Dispatcher
    {
        return $this->__invoke($serviceLocator, Dispatcher::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Dispatcher
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Dispatcher
    {
        $serviceLocator = $container;
        if ($container instanceof ControllerManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }
        $controllerPluginManager = $serviceLocator->get('ControllerPluginManager');
        $cookieService = $serviceLocator->get('Auth\CookieService');
        $logoutService = $serviceLocator->get('Auth\LogoutService');
        $identityProvider = $serviceLocator->get(IdentityProviderInterface::class);
        $controller = new SessionTimeoutController(
            $identityProvider,
            $redirectHelper = $controllerPluginManager->get(Redirect::class),
            $cookieService,
            $logoutService
        );
        // Decorate controller
        $instance = new Dispatcher($controller);
        // Initialize plugins
        $redirectHelper->setController($instance);
        return $instance;
    }
}
