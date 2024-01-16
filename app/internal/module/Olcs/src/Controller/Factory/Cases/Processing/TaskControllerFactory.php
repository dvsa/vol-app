<?php

namespace Olcs\Controller\Factory\Cases\Processing;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Cases\Processing\TaskController;
use Olcs\Service\Data\SubCategory;

class TaskControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return TaskController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TaskController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $router = $container->get('router');
        $subCategoryDataService = $container->get(SubCategory::class);

        return new TaskController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $router,
            $subCategoryDataService
        );
    }
}
