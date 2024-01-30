<?php

namespace Olcs\Controller\Factory;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\IndexController;
use Olcs\Service\Data\DocumentSubCategory;
use Olcs\Service\Data\DocumentSubCategoryWithDocs;
use Olcs\Service\Data\IrhpPermitPrintCountry;
use Olcs\Service\Data\IrhpPermitPrintRangeType;
use Olcs\Service\Data\IrhpPermitPrintStock;
use Olcs\Service\Data\ScannerSubCategory;
use Olcs\Service\Data\SubCategory;
use Olcs\Service\Data\SubCategoryDescription;
use Olcs\Service\Data\TaskSubCategory;
use Olcs\Service\Data\UserListInternal;
use Olcs\Service\Data\UserListInternalExcludingLimitedReadOnlyUsers;
use Psr\Container\ContainerInterface;

class IndexControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IndexController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $userListInternalDataService = $container->get(PluginManager::class)->get(UserListInternal::class);
        $userListInternalExcludingDataService = $container->get(PluginManager::class)->get(UserListInternalExcludingLimitedReadOnlyUsers::class);
        $subCategoryDataService = $container->get(PluginManager::class)->get(SubCategory::class);
        $taskSubCategoryDataService = $container->get(PluginManager::class)->get(TaskSubCategory::class);
        $documentSubCategoryDataService = $container->get(PluginManager::class)->get(DocumentSubCategory::class);
        $documentSubCategoryWithDocsDataService = $container->get(PluginManager::class)->get(DocumentSubCategoryWithDocs::class);
        $scannerSubCategoryDataService = $container->get(PluginManager::class)->get(ScannerSubCategory::class);
        $subCategoryDescriptionDataService = $container->get(PluginManager::class)->get(SubCategoryDescription::class);
        $irhpPermitPrintCountryDataService = $container->get(PluginManager::class)->get(IrhpPermitPrintCountry::class);
        $irhpPermitPrintStockDataService = $container->get(PluginManager::class)->get(IrhpPermitPrintStock::class);
        $irhpPermitPrintRangeTypeDataService = $container->get(PluginManager::class)->get(IrhpPermitPrintRangeType::class);

        return new IndexController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $userListInternalDataService,
            $userListInternalExcludingDataService,
            $subCategoryDataService,
            $taskSubCategoryDataService,
            $documentSubCategoryDataService,
            $documentSubCategoryWithDocsDataService,
            $scannerSubCategoryDataService,
            $subCategoryDescriptionDataService,
            $irhpPermitPrintCountryDataService,
            $irhpPermitPrintStockDataService,
            $irhpPermitPrintRangeTypeDataService
        );
    }
}
