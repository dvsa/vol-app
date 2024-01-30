<?php

namespace Olcs\Controller\Factory\TransportManager;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\TransportManager\TransportManagerController;
use Olcs\Controller\TransportManager\TransportManagerDocumentController;
use Olcs\Service\Data\DocumentSubCategory;
use Psr\Container\ContainerInterface;

class TransportManagerDocumentControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return TransportManagerController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransportManagerController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $navigation = $container->get('Navigation');
        $docSubCategoryDataService = $container->get(PluginManager::class)->get(DocumentSubCategory::class);

        return new TransportManagerDocumentController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $translationHelper,
            $navigation,
            $docSubCategoryDataService
        );
    }
}
