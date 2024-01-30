<?php

namespace Olcs\Controller\Factory\Application\Docs;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Application\Docs\ApplicationDocsController;
use Olcs\Service\Data\DocumentSubCategory;
use Psr\Container\ContainerInterface;

class ApplicationDocsControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return ApplicationDocsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationDocsController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $dataServiceManager = $container->get(PluginManager::class);
        $oppositionHelper = $container->get(OppositionHelperService::class);
        $complaintsHelper = $container->get(ComplaintsHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $docSubCategoryDataService = $container->get(PluginManager::class)->get(DocumentSubCategory::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $navigation = $container->get('Navigation');

        return new ApplicationDocsController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $dataServiceManager,
            $oppositionHelper,
            $complaintsHelper,
            $flashMessengerHelper,
            $docSubCategoryDataService,
            $translationHelper,
            $navigation
        );
    }
}
