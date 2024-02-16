<?php

namespace Olcs\Controller;

use Common\FeatureToggle;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\TaskSearchTrait;
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

class IndexController extends AbstractController implements LeftViewProvider
{
    use TaskSearchTrait;

    protected FlashMessengerHelperService $flashMessengerHelper;
    protected UserListInternal $userListInternalDataService;
    protected UserListInternalExcludingLimitedReadOnlyUsers $userListInternalExcludingDataService;
    protected SubCategory $subCategoryDataService;
    protected TaskSubCategory $taskSubCategoryDataService;
    protected DocumentSubCategory $documentSubCategoryDataService;
    protected DocumentSubCategoryWithDocs $documentSubCategoryWithDocsDataService;
    protected ScannerSubCategory $scannerSubCategoryDataService;
    protected SubCategoryDescription $subCategoryDescriptionDataService;
    protected IrhpPermitPrintCountry $irhpPermitPrintCountryDataService;
    protected IrhpPermitPrintStock $irhpPermitPrintStockDataService;
    protected IrhpPermitPrintRangeType $irhpPermitPrintRangeTypeDataService;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        FlashMessengerHelperService $flashMessengerHelper,
        UserListInternal $userListInternalDataService,
        UserListInternalExcludingLimitedReadOnlyUsers $userListInternalExcludingDataService,
        SubCategory $subCategoryDataService,
        TaskSubCategory $taskSubCategoryDataService,
        DocumentSubCategory $documentSubCategoryDataService,
        DocumentSubCategoryWithDocs $documentSubCategoryWithDocsDataService,
        ScannerSubCategory $scannerSubCategoryDataService,
        SubCategoryDescription $subCategoryDescriptionDataService,
        IrhpPermitPrintCountry $irhpPermitPrintCountryDataService,
        IrhpPermitPrintStock $irhpPermitPrintStockDataService,
        IrhpPermitPrintRangeType $irhpPermitPrintRangeTypeDataService
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager
        );
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->userListInternalDataService = $userListInternalDataService;
        $this->userListInternalExcludingDataService = $userListInternalExcludingDataService;
        $this->subCategoryDataService = $subCategoryDataService;
        $this->taskSubCategoryDataService = $taskSubCategoryDataService;
        $this->documentSubCategoryDataService = $documentSubCategoryDataService;
        $this->documentSubCategoryWithDocsDataService = $documentSubCategoryWithDocsDataService;
        $this->scannerSubCategoryDataService = $scannerSubCategoryDataService;
        $this->subCategoryDescriptionDataService = $subCategoryDescriptionDataService;
        $this->irhpPermitPrintCountryDataService = $irhpPermitPrintCountryDataService;
        $this->irhpPermitPrintStockDataService = $irhpPermitPrintStockDataService;
        $this->irhpPermitPrintRangeTypeDataService = $irhpPermitPrintRangeTypeDataService;
    }

    /**
     * Process action - Index
     *
     * @return bool|\Laminas\Http\Response|ViewModel
     * @throws \Exception
     */
    public function indexAction()
    {
        $redirect = $this->processTasksActions();
        if ($redirect) {
            return $redirect;
        }

        $filters = $this->mapTaskFilters();

        /**
         * @var \Common\Service\Table\TableBuilder $table
        */
        $table = null;

        // assignedToTeam or Category must be selected
        if (
            empty($filters['assignedToTeam'])
            && empty($filters['category'])
        ) {
            $table = $this->getTable('tasks-no-create', []);
            $table->setEmptyMessage('tasks.search.error.filter.needed');

            $this->flashMessengerHelper
                ->addWarningMessage('tasks.search.error.filter.needed');
        } else {
            //  if user specified then remove team from filters (ignore team) @see OLCS-13501
            if (!empty($filters['assignedToUser'])) {
                unset($filters['assignedToTeam']);
            }

            $table = $this->getTaskTable($filters, true);

            $this->loadScripts(['tasks', 'table-actions', 'forms/filter']);
        }

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('pages/table');

        return $this->renderView($view, 'Home');
    }

    /**
     * Build left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $filters = $this->mapTaskFilters();

        $form = $this->getTaskForm($filters)
            ->remove('showTasks');

        $messagingEnabled = $this->handleQuery(IsEnabledQry::create(['ids' => [FeatureToggle::MESSAGING]]))->getResult()['isEnabled'];
        if (!$messagingEnabled) {
            $form->remove('messaging');
        }

        $left = new ViewModel(['form' => $form]);
        $left->setTemplate('sections/home/partials/left');

        return $left;
    }

    /**
     * Retrieve a list of entities, filtered by a certain key.
     * The consumer doesn't control what the entities and keys are; they
     * simply provide a key and a value which we look up in a map
     *
     * @return JsonModel
     */
    public function entityListAction()
    {
        $key = $this->params('type');
        $value = $this->params('value');

        switch ($key) {
            case 'enforcement-area':
                $results = $this->getListDataEnforcementArea($value, 'Please select');
                break;
            case 'task-allocation-users':
                /**
                 * @var \Olcs\Service\Data\UserListInternal $srv
                */
                $srv = $this->userListInternalDataService;
                $srv->setTeamId($value);

                $results = [
                    '' => 'Unassigned',
                    'alpha-split' => 'Alpha split',
                ] +
                $srv->fetchListOptions(null);

                break;
            case 'users-internal':
                /**
                 * @var \Olcs\Service\Data\UserListInternal $srv
                */
                $srv = $this->userListInternalDataService;
                $srv->setTeamId($value);

                $results = [
                    '' => ((int)$value > 0 ? 'Unassigned' : 'Please select'),
                ] + $srv->fetchListOptions(null);

                break;
            case 'users-internal-exclude-limited-read-only':
                /**
                 * @var \Olcs\Service\Data\UserListInternalExcludingLimitedReadOnlyUsers $srv
                */
                $srv = $this->userListInternalExcludingDataService;
                $srv->setTeamId($value);
                $results = [
                    '' => ((int)$value > 0 ? 'Unassigned' : 'Please select'),
                ] + $srv->fetchListOptions(null);

                break;
            case 'users':
                $results = $this->getListDataUser($value, 'All');
                break;
            case 'sub-categories':
                $srv = $this->subCategoryDataService->setCategory($value);
                $results = ['' => 'All'] + $srv->fetchListOptions();
                break;
            case 'sub-categories-no-first-option':
                $results = $this->subCategoryDataService
                    ->setCategory($value)
                    ->fetchListOptions();
                break;
            case 'task-sub-categories':
                $srv = $this->taskSubCategoryDataService->setCategory($value);
                $results = ['' => 'All'] + $srv->fetchListOptions();
                break;
            case 'document-sub-categories':
                $srv = $this->documentSubCategoryDataService->setCategory($value);
                $results = ['' => 'All'] + $srv->fetchListOptions();
                break;
            case 'document-sub-categories-with-docs':
                $srv = $this->documentSubCategoryWithDocsDataService->setCategory($value);
                $results = ['' => 'All'] + $srv->fetchListOptions();
                break;
            case 'scanning-sub-categories':
                $srv = $this->scannerSubCategoryDataService->setCategory($value);
                $results = ['' => 'All'] + $srv->fetchListOptions();
                break;
            case 'document-templates':
                $results = $this->getListDataDocTemplates(null, $value, 'All');
                break;
            case 'sub-category-descriptions':
                $results =  $this->subCategoryDescriptionDataService
                    ->setSubCategory($value)
                    ->fetchListOptions();
                break;
            case 'irhp-permit-print-country':
                $srv = $this->irhpPermitPrintCountryDataService
                    ->setIrhpPermitType($value);
                $results = ['' => 'Please select'] + $srv->fetchListOptions();
                break;
            case 'irhp-permit-print-stock-by-country':
                $srv = $this->irhpPermitPrintStockDataService
                    ->setIrhpPermitType(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID)
                    ->setCountry($value);
                $results = ['' => 'Please select'] + $srv->fetchListOptions();

                break;
            case 'irhp-permit-print-stock-by-type':
                $srv = $this->irhpPermitPrintStockDataService
                    ->setIrhpPermitType($value);
                $results = ['' => 'Please select'] + $srv->fetchListOptions();
                break;
            case 'irhp-permit-print-range-type-by-stock':
                $srv = $this->irhpPermitPrintRangeTypeDataService
                    ->setIrhpPermitStock($value);
                $results = ['' => 'Please select'] + $srv->fetchListOptions();
                break;
            default:
                throw new \Exception('Invalid entity filter key: ' . $key);
        }

        // iterate over the list data and just convert it to a more
        // JS friendly format (key/val assoc isn't quite such a neat
        // fit for frontend)
        $viewResults = [];
        foreach ($results as $id => $result) {
            $viewResults[] = [
                'value' => $id,
                'label' => $result
            ];
        }

        return new JsonModel($viewResults);
    }
}
