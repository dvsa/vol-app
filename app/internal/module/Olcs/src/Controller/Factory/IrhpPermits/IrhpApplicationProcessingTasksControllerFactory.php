<?php

namespace Olcs\Controller\Factory\IrhpPermits;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\IrhpPermits\IrhpApplicationProcessingTasksController;

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
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);

        return new IrhpApplicationProcessingTasksController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return IrhpApplicationProcessingTasksController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): IrhpApplicationProcessingTasksController
    {
        return $this->__invoke($serviceLocator, IrhpApplicationProcessingTasksController::class);
    }
}
