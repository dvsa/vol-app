<?php

namespace Olcs\Controller\Factory;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\TaskController;
use Olcs\Service\Data\SubCategory;
use Olcs\Service\Data\UserListInternalExcludingLimitedReadOnlyUsers;
use Psr\Container\ContainerInterface;

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
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $subCategoryDataService = $container->get(PluginManager::class)->get(SubCategory::class);
        $userListInternalExcLtdRdOnlyDataService = $container->get(PluginManager::class)->get(UserListInternalExcludingLimitedReadOnlyUsers::class);

        return new TaskController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $subCategoryDataService,
            $userListInternalExcLtdRdOnlyDataService
        );
    }
}
