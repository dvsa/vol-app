<?php

namespace Olcs\Controller\Factory\Licence\Processing;

use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Licence\Processing\LicenceProcessingTasksController;
use Olcs\Service\Data\SubCategory;

class LicenceProcessingTasksControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return LicenceProcessingTasksController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceProcessingTasksController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $oppositionHelper = $container->get(OppositionHelperService::class);
        $complaintsHelper = $container->get(ComplaintsHelperService::class);
        $navigation = $container->get('Navigation');
        $subCategoryDataService = $container->get(SubCategory::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $router = $container->get(TreeRouteStack::class);

        return new LicenceProcessingTasksController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $oppositionHelper,
            $complaintsHelper,
            $navigation,
            $subCategoryDataService,
            $flashMessengerHelper,
            $router
        );
    }
}
