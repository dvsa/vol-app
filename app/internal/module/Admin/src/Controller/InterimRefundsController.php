<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\InterimRefundReportFilter as FilterForm;
use Dvsa\Olcs\Transfer\Query\Fee\InterimRefunds as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

/**
 * Class InterimRefundsController
 *
 * @package Admin\Controller
 */
class InterimRefundsController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-report/interim-refunds';

    // list
    protected $tableName = 'admin-interim-refunds-report';
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'ftr.createdOn';
    protected $defaultTableOrderField = 'DESC';
    protected $listDto = ListDto::class;
    protected $filterForm = FilterForm::class;

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions', 'forms/filter'],
    ];


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

    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Interim Refunds');

        $this->setFilterDefaults();

        return parent::indexAction();
    }
}
