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
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;

/**
 * @see ListVehicleController
 */
class ListVehicleControllerFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator): Dispatcher
    {
        return $this->__invoke($serviceLocator, Dispatcher::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return Dispatcher
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Dispatcher
    {
        if ($container instanceof ServiceManager) {
            $sl = $container;
        } else {
            $sl = $container->getServiceLocator();
        }

        $controllerPluginManager = $sl->get('ControllerPluginManager');

        $controller = new ListVehicleController(
            $controllerPluginManager->get(HandleCommand::class),
            $controllerPluginManager->get(HandleQuery::class),
            $sl->get(TranslationHelperService::class),
            $urlHelper = $controllerPluginManager->get(Url::class),
            $sl->get(ResponseHelperService::class),
            $sl->get(TableFactory::class),
            $sl->get(FormHelperService::class),
            $sl->get(FlashMessengerHelperService::class),
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
