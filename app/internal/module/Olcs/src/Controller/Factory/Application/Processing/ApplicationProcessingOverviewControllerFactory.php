<?php

namespace Olcs\Controller\Factory\Application\Processing;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Application\Processing\ApplicationProcessingOverviewController;

class ApplicationProcessingOverviewControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return ApplicationProcessingOverviewController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationProcessingOverviewController
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
        $navigation = $container->get('Navigation');

        return new ApplicationProcessingOverviewController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $dataServiceManager,
            $oppositionHelper,
            $complaintsHelper,
            $flashMessengerHelper,
            $router,
            $navigation
        );
    }
}
