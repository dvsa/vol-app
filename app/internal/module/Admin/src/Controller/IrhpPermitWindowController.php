<?php

namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

use Dvsa\Olcs\Transfer\Query\IrhpPermitWindow\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\IrhpPermitWindow\GetList as ListDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Delete as DeleteDto;
use Admin\Form\Model\Form\IrhpPermitWindow as PermitWindowForm;
use Admin\Data\Mapper\IrhpPermitWindow as PermitWindowMapper;

use Olcs\Mvc\Controller\ParameterProvider\GenericList;
use Zend\View\Model\ViewModel;

/**
 * IRHP Permits Admin Controller
 */
class IrhpPermitWindowController extends AbstractInternalController implements LeftViewProvider
{

    protected $tableName = 'admin-irhp-permit-window';
    protected $defaultTableSortField = 'startDate';
    protected $defaultTableOrderField = 'DESC';

    protected $listVars = ['irhpPermitStock' => 'parentId'];
    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $formClass = PermitWindowForm::class;
    protected $addFormClass = PermitWindowForm::class;
    protected $mapperClass = PermitWindowMapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;
    protected $deleteCommand = DeleteDto::class;

    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove IRHP Permit Window';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this permit window?';
    protected $deleteSuccessMessage = 'The permit window has been removed';
    protected $addContentTitle = 'Add permit window';
    protected $indexPageTitle = 'Permits';

    protected $tableViewTemplate = 'pages/irhp-permit-window/index';
    protected $pageScript= 'irhp-permit-window';

    protected $parentEntity = 'irhpPermitStock';

    protected $navigationId = 'admin-dashboard/admin-permits';

    protected $defaultData = ['parentId' => 'route'];

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
                'navigationTitle' => 'Permits'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }
}
