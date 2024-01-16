<?php

namespace Olcs\Controller\Factory\Bus\Docs;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
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
}
