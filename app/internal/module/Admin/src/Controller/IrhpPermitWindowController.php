<?php

namespace Admin\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Dvsa\Olcs\Transfer\Query\IrhpPermitWindow\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitWindow\GetList as ListDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Delete as DeleteDto;
use Admin\Form\Model\Form\IrhpPermitWindow as PermitWindowForm;
use Admin\Data\Mapper\IrhpPermitWindow as PermitWindowMapper;
use Zend\View\Model\ViewModel;

/**
 * IRHP Permits Admin Controller
 */
class IrhpPermitWindowController extends AbstractIrhpPermitAdminController implements
    LeftViewProvider,
    ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::ADMIN_PERMITS
        ],
    ];

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
    protected $pageScript= 'irhp-permit-window';

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
            $this->redirect()->toRoute('admin-dashboard/admin-permits/permits-system-settings');
        }

        return parent::indexAction();
    }
}
