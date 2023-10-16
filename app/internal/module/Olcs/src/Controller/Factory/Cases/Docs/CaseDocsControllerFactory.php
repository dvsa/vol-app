<?php

namespace Olcs\Controller\Factory\Cases\Docs;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Cases\Docs\CaseDocsController;
use Olcs\Service\Data\DocumentSubCategory;

class CaseDocsControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return CaseDocsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CaseDocsController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $docSubCategoryDataService = $container->get(DocumentSubCategory::class);
        $translationHelper = $container->get(TranslationHelperService::class);

        return new CaseDocsController(
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
     * @return CaseDocsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): CaseDocsController
    {
        return $this->__invoke($serviceLocator, CaseDocsController::class);
    }
}
