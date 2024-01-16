<?php

namespace Olcs\Controller\Factory\IrhpPermits;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\IrhpPermits\IrhpApplicationProcessingTasksController;
use Olcs\Service\Data\SubCategory;

class IrhpApplicationProcessingTasksControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return IrhpApplicationProcessingTasksController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpApplicationProcessingTasksController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $router = $container->get(TreeRouteStack::class);
        $subCategoryDataService = $container->get(SubCategory::class);

        return new IrhpApplicationProcessingTasksController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $router,
            $subCategoryDataService
        );
    }
}
