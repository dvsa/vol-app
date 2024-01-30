<?php

namespace Olcs\Controller\Factory\TransportManager\Processing;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\TransportManager\Processing\TransportManagerProcessingTaskController;
use Olcs\Service\Data\SubCategory;
use Psr\Container\ContainerInterface;

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
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $navigation = $container->get('Navigation');
        $subCategoryDataService = $container->get(PluginManager::class)->get(SubCategory::class);

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
}
