<?php

namespace Olcs\Controller\Factory\Licence\Docs;

use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Licence\Docs\LicenceDocsController;
use Olcs\Service\Data\DocumentSubCategory;

class LicenceDocsControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return LicenceDocsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceDocsController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $docSubCategoryDataService = $container->get(DocumentSubCategory::class);
        $oppositionHelper = $container->get(OppositionHelperService::class);
        $complaintsHelper = $container->get(ComplaintsHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $navigation = $container->get('Navigation');
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new LicenceDocsController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $oppositionHelper,
            $complaintsHelper,
            $translationHelper,
            $docSubCategoryDataService,
            $navigation,
            $flashMessengerHelper
        );
    }
}
