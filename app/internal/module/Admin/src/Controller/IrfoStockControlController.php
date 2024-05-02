<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\IrfoStockControl as Form;
use Admin\Form\Model\Form\IrfoStockControlFilter as FilterForm;
use Admin\Form\Model\Form\IrfoStockControlIssued as IssuedForm;
use Common\RefData;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Irfo\CreateIrfoPermitStock as CreateDto;
use Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoPermitStock as UpdateDto;
use Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoPermitStockIssued as IssuedDto;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPermitStockList as ListDto;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\IrfoStockControl as Mapper;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Olcs\Service\Data\IrfoCountry;

class IrfoStockControlController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-printing/irfo-stock-control';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'serialNo';
    protected $tableName = 'admin-irfo-stock-control';
    protected $listDto = ListDto::class;
    protected $filterForm = FilterForm::class;

    protected $crudConfig = [
        'in-stock' => ['requireRows' => true],
        'issued' => ['requireRows' => true],
        'void' => ['requireRows' => true],
        'returned' => ['requireRows' => true],
    ];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $mapperClass = Mapper::class;

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = CreateDto::class;

    protected $addContentTitle = 'Add IRFO Stock Control';

    public function __construct(
        TranslationHelperService $translationHelperService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation,
        protected DateHelperService $dateHelperService,
        protected IrfoCountry $irfoCountryDataService
    ) {
        parent::__construct($translationHelperService, $formHelper, $flashMessengerHelperService, $navigation);
    }
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-printing',
                'navigationTitle' => 'Printing'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    private function setPageTitle()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'IRFO stock control');
    }

    private function setFilterDefaults()
    {
        /* @var $request \Laminas\Http\Request */
        $request = $this->getRequest();

        $filters = array_merge(
            [
                'validForYear' => $this->dateHelperService->getDate('Y'),
                'order' => 'ASC',
                'limit' => 25
            ],
            $request->getQuery()->toArray()
        );

        if (empty($filters['irfoCountry'])) {
            // if not yet set, set default irfoCountry to the first country on the list
            $irfoCountries = $this->irfoCountryDataService
                ->fetchListData();
            if (!empty($irfoCountries)) {
                $filters['irfoCountry'] = $irfoCountries[0]['id'];
            }
        }

        $request->getQuery()->fromArray($filters);
    }

    public function indexAction()
    {
        $this->setPageTitle();

        $this->setFilterDefaults();

        return parent::indexAction();
    }

    public function detailsAction()
    {
        return $this->notFoundAction();
    }

    public function editAction()
    {
        return $this->notFoundAction();
    }

    public function deleteAction()
    {
        return $this->notFoundAction();
    }

    public function inStockAction()
    {
        return $this->update(RefData::IRFO_STOCK_CONTROL_STATUS_IN_STOCK);
    }

    public function issuedAction()
    {
        $request = $this->getRequest();

        $form = $this->getForm(IssuedForm::class);

        if ($request->isPost()) {
            $dataFromPost = (array) $this->params()->fromPost();
            $form->setData($dataFromPost);
        }

        if ($request->isPost() && $form->isValid()) {
            $response = $this->handleCommand(
                IssuedDto::create(
                    [
                        'ids' => explode(',', $this->params()->fromRoute('id')),
                        'irfoGvPermit' => $form->getData()['fields']['irfoGvPermitId']
                    ]
                )
            );

            if ($response->isOk()) {
                $this->flashMessengerHelperService->addSuccessMessage('Update successful');
                return $this->redirectTo($response->getResult());
            } else {
                $this->handleErrors($response->getResult());
            }
        }

        $this->placeholder()->setPlaceholder('form', $form);
        return $this->viewBuilder()->buildViewFromTemplate('pages/crud-form');
    }

    public function voidAction()
    {
        return $this->update(RefData::IRFO_STOCK_CONTROL_STATUS_VOID);
    }

    public function returnedAction()
    {
        return $this->update(RefData::IRFO_STOCK_CONTROL_STATUS_RETURNED);
    }

    protected function update($status)
    {
        return $this->processCommand(
            new AddFormDefaultData(['ids' => explode(',', $this->params()->fromRoute('id')), 'status' => $status]),
            UpdateDto::class
        );
    }
}
