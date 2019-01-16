<?php

namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;

use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\GetList as ListDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Query\ContactDetail\CountrySelectList as CountrySelectListDTO;
use Admin\Form\Model\Form\IrhpPermitStock as PermitStockForm;
use Admin\Data\Mapper\IrhpPermitStock as PermitStockMapper;

use Zend\View\Model\ViewModel;

/**
 * IRHP Permits Admin Controller
 */
class IrhpPermitStockController extends AbstractInternalController implements LeftViewProvider, ToggleAwareInterface
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-permits';

    protected $toggleConfig = [
        'default' => [
            FeatureToggle::ADMIN_PERMITS
        ],
    ];

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'addAction' => ['forms/irhp-permit-stock-modal'],
        'editAction' => ['forms/irhp-permit-stock-modal'],
    ];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableName = 'admin-irhp-permit-stock';
    protected $defaultTableSortField = 'validFrom';
    protected $defaultTableOrderField = 'DESC';
    protected $listDto = ListDto::class;

    protected $itemDto = ItemDto::class;
    protected $formClass = PermitStockForm::class;
    protected $addFormClass = PermitStockForm::class;
    protected $mapperClass = PermitStockMapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;

    protected $deleteCommand = DeleteDto::class;
    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove IRHP Permit Stock';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this permit stock?';
    protected $deleteSuccessMessage = 'The permit stock has been removed';

    protected $addContentTitle = 'Add permit stock';

    protected $tableViewTemplate = 'pages/irhp-permit-stock/index';

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
                'navigationTitle' => 'Permits system settings',
                'singleNav' => true
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Permit Stock Index View
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $this->getServiceLocator()->get('Script')->loadFile('irhp-permit-stock');
        $this->placeholder()->setPlaceholder('pageTitle', 'Permits');

        return parent::indexAction();
    }

    /**
     * Setup required values for Add form
     *
     * @param $form
     * @param $formData
     * @return mixed
     *
     */
    protected function alterFormForAdd($form, $formData)
    {
        return $this->retrieveEeaCountries($form);
    }

    /**
     * Setup required values for Edit form
     *
     * @param $form
     * @param $formData
     * @return mixed
     *
     */
    protected function alterFormForEdit($form, $formData)
    {
        return $this->retrieveEeaCountries($form);
    }

    /**
     * Perform query to retrieve EEA country list for dropdown on add/edit
     *
     * @param $form
     * @return mixed
     */
    protected function retrieveEeaCountries($form)
    {
        $response = $this->handleQuery(CountrySelectListDTO::create([
            'isEeaState' => 1,
        ]));

        if ($response->isOk()) {
            $data = $response->getResult();
        } else {
            $this->handleErrors($response->getResult());
            $data['results'] = [];
        }

        $form->get('permitStockDetails')
            ->get('country')
            ->setValueOptions(
                PermitStockMapper::mapCountryOptions($data['results'])
            );

        return $form;
    }
}
