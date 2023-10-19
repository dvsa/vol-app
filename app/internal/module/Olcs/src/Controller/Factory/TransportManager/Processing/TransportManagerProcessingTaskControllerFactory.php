<?php

namespace Olcs\Controller\Factory\TransportManager\Processing;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\TransportManager\Processing\TransportManagerProcessingTaskController;
use Olcs\Service\Data\SubCategory;

class TransportManagerProcessingTaskControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return TransportManagerProcessingTaskController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransportManagerProcessingTaskController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $navigation = $container->get('navigation');
        $subCategoryDataService = $container->get(SubCategory::class);

        return new TransportManagerProcessingTaskController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $translationHelper,
            $navigation,
            $subCategoryDataService
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return TransportManagerProcessingTaskController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): TransportManagerProcessingTaskController
    {
        return $this->__invoke($serviceLocator, TransportManagerProcessingTaskController::class);
    }
}
