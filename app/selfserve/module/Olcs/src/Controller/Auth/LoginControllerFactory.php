<?php

declare(strict_types=1);

namespace Olcs\Controller\Auth;

use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Dispatcher;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Service\FlashMessenger\LaminasSessionFlashMessenger;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
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
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Dispatcher
    {
        $controllerPluginManager = $container->get('ControllerPluginManager');

        $controller = new LoginController(
            $container->get(SelfserveCommandAdapter::class),
            $container->get(AuthenticationServiceInterface::class),
            $controllerPluginManager->get(CurrentUser::class),
            $container->get(LaminasSessionFlashMessenger::class),
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
}
