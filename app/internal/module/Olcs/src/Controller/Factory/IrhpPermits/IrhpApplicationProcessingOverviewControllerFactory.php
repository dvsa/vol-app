<?php

namespace Olcs\Controller\Factory\IrhpPermits;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\Mvc\Router\Http\TreeRouteStack;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
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
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

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

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return IrhpApplicationProcessingOverviewController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): IrhpApplicationProcessingOverviewController
    {
        return $this->__invoke($serviceLocator, IrhpApplicationProcessingOverviewController::class);
    }
}
