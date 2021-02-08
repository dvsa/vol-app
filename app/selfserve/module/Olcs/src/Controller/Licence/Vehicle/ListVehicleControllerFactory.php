<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Dispatcher;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\TranslationHelperService;
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
        assert($serviceLocator instanceof ControllerManager, 'Expected instance of ControllerManager');
        $serviceLocator = $serviceLocator->getServiceLocator();
        $controllerPluginManager = $serviceLocator->get('ControllerPluginManager');
        $translationService = new TranslationHelperService();
        $translationService->setServiceLocator($serviceLocator);
        $queryHandler = $controllerPluginManager->get('handleQuery');
        $urlHelper = new Url();
        $responseHelper = new ResponseHelperService();
        $tableFactory = $serviceLocator->get('Table');
        $formHelper = $serviceLocator->get('Helper\Form');
        $controller = new ListVehicleController($queryHandler, $translationService, $urlHelper, $responseHelper, $tableFactory, $formHelper);

        // Decorate controller
        $instance = new Dispatcher($controller);

        // Initialize plugins
        $urlHelper->setController($instance);

        return $instance;
    }
}
