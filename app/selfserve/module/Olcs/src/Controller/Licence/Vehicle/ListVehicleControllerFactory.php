<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Dispatcher;
use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @see ListVehicleController
 */
class ListVehicleControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Dispatcher
    {
        $controllerPluginManager = $container->get('ControllerPluginManager');

        $controller = new ListVehicleController(
            $controllerPluginManager->get(HandleCommand::class),
            $controllerPluginManager->get(HandleQuery::class),
            $container->get(TranslationHelperService::class),
            $urlHelper = $controllerPluginManager->get(Url::class),
            $container->get(ResponseHelperService::class),
            $container->get(TableFactory::class),
            $container->get(FormHelperService::class),
            $container->get(FlashMessengerHelperService::class),
            $redirectHelper = $controllerPluginManager->get(Redirect::class)
        );
        // Decorate controller
        $instance = new Dispatcher($controller);
        // Initialize plugins
        $urlHelper->setController($instance);
        $redirectHelper->setController($instance);
        return $instance;
    }
}
