<?php

namespace Olcs\Controller\Factory\IrhpPermits;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\IrhpPermits\IrhpApplicationDocsController;
use Olcs\Service\Data\DocumentSubCategory;

class IrhpApplicationDocsControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return IrhpApplicationDocsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpApplicationDocsController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $docSubCategoryDataService = $container->get(DocumentSubCategory::class);
        $translationHelper = $container->get(TranslationHelperService::class);

        return new IrhpApplicationDocsController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $docSubCategoryDataService,
            $translationHelper
        );
    }
}
