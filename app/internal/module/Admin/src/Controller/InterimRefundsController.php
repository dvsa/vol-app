<?php

namespace Admin\Controller;

use Admin\Controller\Traits\ReportLeftViewTrait;
use Admin\Form\Model\Form\InterimRefundReportFilter as FilterForm;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\Fee\InterimRefunds as ListDto;
use Laminas\Navigation\Navigation;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class InterimRefundsController extends AbstractInternalController implements LeftViewProvider
{
    use ReportLeftViewTrait;

    protected $navigationId = 'admin-dashboard/admin-report/interim-refunds';

    // list
    protected $tableName = 'admin-interim-refunds-report';
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'ftr.createdOn';
    protected $defaultTableOrderField = 'DESC';
    protected $listDto = ListDto::class;
    protected $filterForm = FilterForm::class;

    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessenger,
        Navigation $navigation,
        protected DateHelperService $dateHelper
    ) {
        parent::__construct($translationHelper, $formHelper, $flashMessenger, $navigation);
    }

    /**
     * Sets filter defaults
     *
     * @return void
     */
    private function setFilterDefaults()
    {
        /* @var $request \Laminas\Http\Request */
        $request = $this->getRequest();

        $eomDate = $this->dateHelper->getDate('Y-m-t');
        [$year, $month, $lastDay] = explode('-', $eomDate);

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
