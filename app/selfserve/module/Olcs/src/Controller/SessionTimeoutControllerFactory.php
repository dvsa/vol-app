<?php

declare(strict_types=1);

namespace Olcs\Controller;

use Common\Controller\Dispatcher;
use Common\Controller\Plugin\Redirect;
use Interop\Container\ContainerInterface;
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
     */
    public function createService(ServiceLocatorInterface $serviceLocator): Dispatcher
    {
        return $this->__invoke($serviceLocator, Dispatcher::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Dispatcher
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }

        $controllerPluginManager = $container->get('ControllerPluginManager');
        $cookieService = $container->get('Auth\CookieService');
        $logoutService = $container->get('Auth\LogoutService');
        $identityProvider = $container->get(IdentityProviderInterface::class);
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
