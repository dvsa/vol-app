<?php

namespace Olcs\Controller\Factory\Cases\Docs;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Cases\Docs\CaseDocsController;
use Olcs\Service\Data\DocumentSubCategory;
use Psr\Container\ContainerInterface;

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
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $docSubCategoryDataService = $container->get(PluginManager::class)->get(DocumentSubCategory::class);
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
}
