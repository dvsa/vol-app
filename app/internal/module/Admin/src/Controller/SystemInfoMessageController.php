<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\SystemInfoMessage as AddEditForm;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Transfer\Command\System\InfoMessage\Create as CreateCmd;
use Dvsa\Olcs\Transfer\Command\System\InfoMessage\Delete as DeleteCmd;
use Dvsa\Olcs\Transfer\Command\System\InfoMessage\Update as UpdateCmd;
use Dvsa\Olcs\Transfer\Query\System\InfoMessage\Get as ItemQry;
use Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetList as ListQry;
use Olcs\Controller\AbstractInternalController;
use Olcs\Data\Mapper\SystemInfoMessage as AddEditFormMapper;
use Zend\View\Model\ViewModel;

/**
 * System Info Messages Controller
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class SystemInfoMessageController extends AbstractInternalController
{
    const ROUTE = 'admin-dashboard/admin-system-info-message';

    /** @var \Common\Service\Helper\FormHelperService */
    protected $formHelper;

    protected $navigationId = 'admin-dashboard/admin-system-info-message';

    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    protected $routeIdentifier = 'msgId';

    //  list
    protected $tableName = 'admin-system-info-message';
    protected $defaultTableSortField = 'startDate';
    protected $defaultTableOrderField = 'ASC';
    protected $listDto = ListQry::class;

    //  add/edit
    protected $itemDto = ItemQry::class;
    protected $itemParams = ['id' => 'msgId'];
    protected $formClass = AddEditForm::class;
    protected $mapperClass = AddEditFormMapper::class;
    protected $createCommand = CreateCmd::class;
    protected $updateCommand = UpdateCmd::class;

    protected $addContentTitle = 'Add system message';
    protected $addSuccessMessage = 'New system message created';
    protected $editContentTitle = 'Edit system message';
    protected $editSuccessMessage = 'System message updated';

    //  delete
    protected $deleteParams = ['id' => 'msgId'];
    protected $deleteCommand = DeleteCmd::class;
    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove system info message';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this system info message?';
    protected $deleteSuccessMessage = 'The system info message is removed';

    protected $tableViewTemplate = 'pages/system/info-messages';

    /**
     * @param FormHelperService $formHelper
     */
    public function __construct(FormHelperService $formHelper)
    {
        $this->formHelper = $formHelper;
    }

    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'System messages');

        return parent::indexAction();
    }

    protected function alterFormForAdd($form)
    {
        $this->formHelper->remove($form, 'form-actions->addAnother');

        return $form;
    }

    protected function alterFormForEdit($form)
    {
        $this->formHelper->remove($form, 'form-actions->addAnother');

        return $form;
    }
}
