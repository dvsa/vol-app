<?php

namespace Olcs\Controller\Factory\Document;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Document\DocumentGenerationController;
use Olcs\Service\Data\DocumentSubCategoryWithDocs;

class DocumentGenerationControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return DocumentGenerationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DocumentGenerationController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $config = $container->get('Config');
        $flashMessangerHelper = $container->get(FlashMessengerHelperService::class);
        $docSubcategoryWithDocsDataService = $container->get(DocumentSubCategoryWithDocs::class);

        return new DocumentGenerationController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $config,
            $flashMessangerHelper,
            $docSubcategoryWithDocsDataService
        );
    }
}
