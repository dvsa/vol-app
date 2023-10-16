<?php

namespace Olcs\Controller\Factory\Licence\Processing;

use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Licence\Processing\LicenceProcessingOverviewController;
use Olcs\Service\Data\SubCategory;

class LicenceProcessingOverviewControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return LicenceProcessingOverviewController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceProcessingOverviewController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $oppositionHelper = $container->get(OppositionHelperService::class);
        $complaintsHelper = $container->get(ComplaintsHelperService::class);
        $navigation = $container->get('navigation');
        $subCategoryDataService = $container->get(SubCategory::class);

        return new LicenceProcessingOverviewController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $oppositionHelper,
            $complaintsHelper,
            $navigation,
            $subCategoryDataService
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return LicenceProcessingOverviewController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): LicenceProcessingOverviewController
    {
        return $this->__invoke($serviceLocator, LicenceProcessingOverviewController::class);
    }
}
