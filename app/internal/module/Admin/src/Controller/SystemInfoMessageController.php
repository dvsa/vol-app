<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\System\InfoMessage\Delete as DeleteCmd;
use Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetList as ListQry;
use Olcs\Controller\AbstractInternalController;
use Zend\View\Model\ViewModel;

/**
 * System Info Messages Controller
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class SystemInfoMessageController extends AbstractInternalController
{
    const ROUTE = 'admin-dashboard/admin-system-info-message';
    const ROUTE_DELETE = 'admin-dashboard/admin-system-info-message/delete/:id';

    protected $navigationId = 'admin-dashboard/admin-system-info-message';

    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    //  list
    protected $tableName = 'admin-system-info-message';
    protected $defaultTableSortField = 'startDate';
    protected $defaultTableOrderField = 'ASC';
    protected $listDto = ListQry::class;

    protected $routeIdentifier = 'msgId';

    // delete
    protected $deleteParams = ['id' => 'msgId'];
    protected $deleteCommand = DeleteCmd::class;
    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove system info message';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this system info message?';
    protected $deleteSuccessMessage = 'The system info message is removed';

    protected $tableViewTemplate = 'pages/system/info-messages';

    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'System messages');

        return parent::indexAction();
    }
}
