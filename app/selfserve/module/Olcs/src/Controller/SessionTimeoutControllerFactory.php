<?php

declare(strict_types=1);

namespace Olcs\Controller;

use Common\Controller\Dispatcher;
use Common\Controller\Plugin\Redirect;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * @See SessionTimeoutController
 */
class SessionTimeoutControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Dispatcher
    {
        $controllerPluginManager = $container->get('ControllerPluginManager');
        $identityProvider = $container->get(IdentityProviderInterface::class);
        $controller = new SessionTimeoutController(
            $identityProvider,
            $redirectHelper = $controllerPluginManager->get(Redirect::class),
        );
        // Decorate controller
        $instance = new Dispatcher($controller);
        // Initialize plugins
        $redirectHelper->setController($instance);
        return $instance;
    }
}
