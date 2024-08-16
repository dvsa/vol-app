<?php

namespace Admin\Controller;

use Admin\Controller\Traits\ReportLeftViewTrait;
use Admin\Form\Model\Form;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class ReportCasesOpenController extends AbstractInternalController implements LeftViewProvider
{
    use ReportLeftViewTrait;

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
    protected $defaultTableLimit = 25;
    protected $tableName = 'admin-cases-open-report';

    protected $listDto = TransferQry\Cases\Report\OpenList::class;

    protected $filterForm = Form\CasesOpenReportFilter::class;

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
