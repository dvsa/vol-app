<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
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
        $userListInternalDataService = $container->get(UserListInternal::class);
        $userListInternalExcludingDataService = $container->get(UserListInternalExcludingLimitedReadOnlyUsers::class);
        $subCategoryDataService = $container->get(SubCategory::class);
        $taskSubCategoryDataService = $container->get(TaskSubCategory::class);
        $documentSubCategoryDataService = $container->get(DocumentSubCategory::class);
        $documentSubCategoryWithDocsDataService = $container->get(DocumentSubCategoryWithDocs::class);
        $scannerSubCategoryDataService = $container->get(ScannerSubCategory::class);
        $subCategoryDescriptionDataService = $container->get(SubCategoryDescription::class);
        $irhpPermitPrintCountryDataService = $container->get(IrhpPermitPrintCountry::class);
        $irhpPermitPrintStockDataService = $container->get(IrhpPermitPrintStock::class);
        $irhpPermitPrintRangeTypeDataService = $container->get(IrhpPermitPrintRangeType::class);

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
