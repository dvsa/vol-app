<?php

namespace Olcs\Controller\Factory\Bus\Docs;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Bus\Docs\BusDocsController;
use Olcs\Service\Data\DocumentSubCategory;

class BusDocsControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return BusDocsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BusDocsController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $docSubCategoryDataService = $container->get(DocumentSubCategory::class);
        $translationHelper = $container->get(TranslationHelperService::class);

        return new BusDocsController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $translationHelper,
            $docSubCategoryDataService
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return BusDocsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): BusDocsController
    {
        return $this->__invoke($serviceLocator, BusDocsController::class);
    }
}
