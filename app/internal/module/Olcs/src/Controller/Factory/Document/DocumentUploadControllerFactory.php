<?php

namespace Olcs\Controller\Factory\Document;

use Common\Service\AntiVirus\Scan;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Document\DocumentUploadController;
use Olcs\Service\Data\DocumentSubCategory;

class DocumentUploadControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return DocumentUploadController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DocumentUploadController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $config = $container->get('Config');
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $documentSubCategoryDataService = $container->get(DocumentSubCategory::class);
        $avScan = $container->get(Scan::class);

        return new DocumentUploadController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $config,
            $flashMessengerHelper,
            $documentSubCategoryDataService,
            $avScan
        );
    }
}
