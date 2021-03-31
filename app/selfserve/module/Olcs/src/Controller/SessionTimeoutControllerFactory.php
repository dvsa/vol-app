<?php
declare(strict_types=1);

namespace Olcs\Controller;

use Common\Controller\Dispatcher;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\ControllerManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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

        $controller = new SessionTimeoutController(
            $currentUser = $controllerPluginManager->get(CurrentUser::class),
            $redirectHelper = $controllerPluginManager->get(Redirect::class),
            $cookieService,
            $logoutService
        );

        // Decorate controller
        $instance = new Dispatcher($controller);

        // Initialize plugins
        $currentUser->setController($instance);
        $redirectHelper->setController($instance);

        return $instance;
    }
}
