<?php

namespace Olcs\Controller\Factory\Application\Processing;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Application\Processing\ApplicationProcessingTasksController;
use Olcs\Service\Data\SubCategory;
use Psr\Container\ContainerInterface;

class ApplicationProcessingTasksControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return ApplicationProcessingTasksController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationProcessingTasksController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $dataServiceManager = $container->get(PluginManager::class);
        $oppositionHelper = $container->get(OppositionHelperService::class);
        $complaintsHelper = $container->get(ComplaintsHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $router = $container->get('router');
        $subCategoryDataService = $container->get(PluginManager::class)->get(SubCategory::class);
        $navigation = $container->get('navigation');

        return new ApplicationProcessingTasksController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $dataServiceManager,
            $oppositionHelper,
            $complaintsHelper,
            $flashMessengerHelper,
            $router,
            $subCategoryDataService,
            $navigation
        );
    }
}
