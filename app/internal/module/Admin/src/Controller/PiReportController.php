<?php

/**
 * PI Report Controller
 */
namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Query\Cases\Pi\ReportList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Admin\Form\Model\Form\PiReportFilter as FilterForm;
use Zend\View\Model\ViewModel;

/**
 * PI Report Controller
 */
class PiReportController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-report/pi';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'hearingDate';
    protected $defaultTableOrderField = 'ASC';
    protected $tableName = 'admin-pi-report';
    protected $listDto = ListDto::class;
    protected $filterForm = FilterForm::class;

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
                'navigationTitle' => 'Reports'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Sets the page title
     *
     * @return void
     */
    private function setPageTitle()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Public Inquiry listings');
    }

    /**
     * Sets filter defaults
     *
     * @return void
     */
    private function setFilterDefaults()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();

        $eomDate = $this->getServiceLocator()->get('Helper\Date')->getDate('Y-m-t');
        list($year, $month, $lastDay) = explode('-', $eomDate);

        $filters = array_merge(
            [
                'startDate' => [
                    'day' => 1,
                    'month' => $month,
                    'year' => $year,
                ],
                'endDate' => [
                    'day' => $lastDay,
                    'month' => $month,
                    'year' => $year,
                ],
            ],
            $request->getQuery()->toArray()
        );

        $request->getQuery()->fromArray($filters);
    }

    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $this->setPageTitle();

        $this->setFilterDefaults();

        return parent::indexAction();
    }
}
