<?php

namespace Admin\Controller;

use Admin\Data\Mapper\IrhpPermitWindow as PermitWindowMapper;
use Admin\Form\Model\Form\IrhpPermitWindow as PermitWindowForm;
use Common\Form\Form;
use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\ById as GetIrhpPermitStockByIdDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitWindow\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitWindow\GetList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;

class IrhpPermitWindowController extends AbstractIrhpPermitAdminController implements LeftViewProvider
{
    protected $tableName = 'admin-irhp-permit-window';
    protected $defaultTableSortField = 'startDate';
    protected $defaultTableOrderField = 'DESC';

    protected $listVars = ['irhpPermitStock' => 'stockId'];
    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $formClass = PermitWindowForm::class;
    protected $mapperClass = PermitWindowMapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;
    protected $deleteCommand = DeleteDto::class;

    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove Permit Window';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this Permit Window?';
    protected $deleteSuccessMessage = 'The Permit Window has been removed';
    protected $addContentTitle = 'Add Permit Window';
    protected $addSuccessMessage = 'New Permit Window created';
    protected $indexPageTitle = 'Permits';

    protected $tableViewTemplate = 'pages/irhp-permit-window/index';
    protected $pageScript = 'irhp-permit-window';

    protected $navigationId = 'admin-dashboard/admin-permits';

    protected $defaultData = ['stockId' => 'route'];

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-permits',
                'navigationTitle' => '',
                'stockId' => $this->params()->fromRoute()['stockId']
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    #[\Override]
    public function indexAction()
    {
        // If an IRHP Permit Stock ID is not specified then redirect the user to the Permits System Settings page.
        if (!isset($this->params()->fromRoute()['stockId'])) {
            $this->redirect()->toRoute('admin-dashboard/admin-permits/stocks');
        }

        return parent::indexAction();
    }

    /**
     * Alter form for add
     *
     * @param Form  $form     Form
     * @param array $formData Form data
     *
     * @return                                        Form
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function alterFormForAdd(Form $form, array $formData)
    {
        return $this->alterForm($form);
    }

    /**
     * Alter form for edit
     *
     * @param Form  $form     Form
     * @param array $formData Form data
     *
     * @return                                        Form
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function alterFormForEdit(Form $form, array $formData)
    {
        return $this->alterForm($form);
    }

    /**
     * Alter form
     *
     * @param Form $form Form
     *
     * @return Form
     */
    private function alterForm(Form $form)
    {
        $irhpPermitStock = $this->retrieveIrhpPermitStock($this->params()->fromRoute()['stockId']);

        if (!empty($irhpPermitStock) && !$irhpPermitStock['irhpPermitType']['isEcmtAnnual']) {
            // emissionsCategory only required for ECMT
            $form->get('permitWindowDetails')
                ->remove('emissionsCategory');
        }

        return $form;
    }

    /**
     * Retrieve Irhp Permit Stock data
     *
     * @param int $id Irhp Permit Stock id
     *
     * @return array
     */
    private function retrieveIrhpPermitStock($id)
    {
        $data = [];

        $response = $this->handleQuery(
            GetIrhpPermitStockByIdDto::create(['id' => $id])
        );

        if ($response->isOk()) {
            $data = $response->getResult();
        } else {
            $this->handleErrors($response->getResult());
        }

        return $data;
    }
}
