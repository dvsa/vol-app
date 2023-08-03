<?php
declare(strict_types=1);

namespace Olcs\Controller\Auth;

use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Dispatcher;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Interop\Container\ContainerInterface;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Auth\Adapter\SelfserveCommandAdapter;

class LoginControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Dispatcher
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Dispatcher
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $controllerPluginManager = $container->get('ControllerPluginManager');

        $controller = new LoginController(
            $container->get(SelfserveCommandAdapter::class),
            $container->get(AuthenticationServiceInterface::class),
            $controllerPluginManager->get(CurrentUser::class),
            $controllerPluginManager->get(FlashMessenger::class),
            $container->get(FormHelperService::class),
            $redirectHelper = $controllerPluginManager->get(Redirect::class),
            new AuthChallengeContainer()
        );

        // Decorate controller
        $instance = new Dispatcher($controller);

        // Initialize plugins
        $redirectHelper->setController($instance);

        return $instance;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return Dispatcher
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator): Dispatcher
    {
        return $this->__invoke($serviceLocator, Dispatcher::class);
    }
}
