<?php
declare(strict_types=1);

namespace Olcs\Controller;

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
     * @inheritDoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if ($serviceLocator instanceof ControllerManager) {
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
