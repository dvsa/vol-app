<?php

namespace Olcs\Controller\Factory\IrhpPermits;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Psr\Container\ContainerInterface;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\IrhpPermits\IrhpApplicationProcessingOverviewController;
use Olcs\Helper\ApplicationProcessingHelper;

class IrhpApplicationProcessingOverviewControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return IrhpApplicationProcessingOverviewController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpApplicationProcessingOverviewController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $router = $container->get(TreeRouteStack::class);
        $processingHelper = $container->get(ApplicationProcessingHelper::class);

        return new IrhpApplicationProcessingOverviewController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $router,
            $processingHelper
        );
    }
}
