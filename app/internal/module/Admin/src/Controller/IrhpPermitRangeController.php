<?php

namespace Admin\Controller;

use Admin\Data\Mapper\IrhpPermitRange as PermitRangeMapper;
use Admin\Form\Model\Form\IrhpPermitRange as PermitRangeForm;
use Common\Form\Form;
use Dvsa\Olcs\Transfer\Command\IrhpPermitRange\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitRange\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitRange\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitRange\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitRange\GetList as ListDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\ById as GetIrhpPermitStockByIdDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;

class IrhpPermitRangeController extends AbstractIrhpPermitAdminController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $tableName = 'admin-irhp-permit-range';
    protected $defaultTableSortField = 'fromNo';
    protected $defaultTableOrderField = 'ASC';

    protected $listVars = ['irhpPermitStock' => 'stockId'];
    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $formClass = PermitRangeForm::class;
    protected $addFormClass = PermitRangeForm::class;
    protected $mapperClass = PermitRangeMapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;
    protected $deleteCommand = DeleteDto::class;

    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove IRHP Permit Range';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this permit range?';
    protected $deleteSuccessMessage = 'The permit range has been removed';
    protected $addContentTitle = 'Add permit range';
    protected $indexPageTitle = 'Permits';

    protected $tableViewTemplate = 'pages/irhp-permit-range/index';

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

    public function indexAction()
    {
        // If an IRHP Permit Stock ID is not specified then redirect the user to the Permits System Settings page.
        if (!isset($this->params()->fromRoute()['stockId'])) {
            $this->redirect()->toRoute($this->navigationId . '/stocks');
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

        if (empty($irhpPermitStock)) {
            return $form;
        }

        $permitRangeDetails = $form->get('permitRangeDetails');

        $inputFilter = $form->getInputFilter();
        $permitRangeDetailsFilter = $inputFilter->get('permitRangeDetails');

        if (!$irhpPermitStock['irhpPermitType']['isEcmtShortTerm'] && !$irhpPermitStock['irhpPermitType']['isEcmtAnnual']) {
            // emissionsCategory only required for Short-term or annual ECMT
            $permitRangeDetails->remove('emissionsCategory');
            $permitRangeDetailsFilter->remove('emissionsCategory');
        }

        if (!$irhpPermitStock['irhpPermitType']['isBilateral']) {
            // journey and cabotage only required for Bilateral
            $permitRangeDetails->remove('journey');
            $permitRangeDetails->remove('cabotage');

            $permitRangeDetailsFilter->remove('journey');
            $permitRangeDetailsFilter->remove('cabotage');
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
