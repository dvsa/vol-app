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
use Laminas\Mvc\Controller\ControllerManager;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @see ListVehicleController
 */
class ListVehicleControllerFactory implements FactoryInterface
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

        $controller = new ListVehicleController(
            $controllerPluginManager->get(HandleCommand::class),
            $controllerPluginManager->get(HandleQuery::class),
            $serviceLocator->get(TranslationHelperService::class),
            $urlHelper = $controllerPluginManager->get(Url::class),
            $serviceLocator->get(ResponseHelperService::class),
            $serviceLocator->get(TableFactory::class),
            $serviceLocator->get(FormHelperService::class),
            $serviceLocator->get(FlashMessengerHelperService::class),
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
