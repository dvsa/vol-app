<?php
declare(strict_types=1);

namespace Olcs\Controller\Auth;

use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Dispatcher;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Psr\Container\ContainerInterface;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Layout;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Auth\Adapter\InternalCommandAdapter;

class LoginControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Dispatcher
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Dispatcher
    {
        $controllerPluginManager = $container->get('ControllerPluginManager');

        $controller = new LoginController(
            $container->get(InternalCommandAdapter::class),
            $container->get(AuthenticationServiceInterface::class),
            $container->get('Auth\CookieService'),
            $controllerPluginManager->get(CurrentUser::class),
            $controllerPluginManager->get(FlashMessenger::class),
            $container->get(FormHelperService::class),
            $layoutHelper = $controllerPluginManager->get(Layout::class),
            $redirectHelper = $controllerPluginManager->get(Redirect::class),
            $urlHelper = $controllerPluginManager->get(Url::class),
            new AuthChallengeContainer()
        );

        // Decorate controller
        $instance = new Dispatcher($controller);

        // Initialize plugins
        $layoutHelper->setController($instance);
        $urlHelper->setController($instance);
        $redirectHelper->setController($instance);

        return $instance;
    }
}
