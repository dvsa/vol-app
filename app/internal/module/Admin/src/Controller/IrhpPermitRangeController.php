<?php

namespace Admin\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;

use Admin\Controller\AbstractIrhpPermitAdminController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Dvsa\Olcs\Transfer\Query\IrhpPermitRange\GetList as ListDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitRange\ById as ItemDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitRange\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitRange\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitRange\Delete as DeleteDto;
use Admin\Form\Model\Form\IrhpPermitRange as PermitRangeForm;
use Admin\Data\Mapper\IrhpPermitRange as PermitRangeMapper;

use Zend\View\Model\ViewModel;

/**
 * IRHP Permits Stock Range Controller
 */
class IrhpPermitRangeController extends AbstractIrhpPermitAdminController implements
    LeftViewProvider,
    ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::ADMIN_PERMITS
        ],
    ];

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
            $this->redirect()->toRoute($this->navigationId . '/permits-system-settings');
        }

        return parent::indexAction();
    }
}
