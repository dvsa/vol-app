<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\Permits\TriggerProcessEcmtApplications;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;

use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\GetList as ListDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Delete as DeleteDto;
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

    public function triggerAction()
    {
        $response = $this->handleCommand(TriggerProcessEcmtApplications::create([]));
        $view = new ViewModel(
            [
                'triggerOutput' => $response->getResult(),
            ]
        );
        $view->setTemplate('pages/irhp-permit-stock/index');

        return $view;
    }
}
