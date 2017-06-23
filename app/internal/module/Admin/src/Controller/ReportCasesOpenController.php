<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Query as TransferQry;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

/**
 * @author Dmitry Golubev <d.e.golubev@gmail.com>
 */
class ReportCasesOpenController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-report/cases/open';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $defaultTableSortField = 'openDate';
    protected $defaultTableOrderField = 'ASC';
    protected $tableName = 'admin-cases-open-report';

    protected $listDto = TransferQry\Cases\Report\OpenList::class;

    /**
     * Gets left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-report',
                'navigationTitle' => 'Reports',
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Open cases report');

        return parent::indexAction();
    }
}
