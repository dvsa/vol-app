<?php

/**
 * PI Report Controller
 */

namespace Admin\Controller;

use Admin\Controller\Traits\ReportLeftViewTrait;
use Admin\Form\Model\Form\PiReportFilter as FilterForm;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\ReportList as ListDto;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * PI Report Controller
 */
class PiReportController extends AbstractInternalController implements LeftViewProvider
{
    use ReportLeftViewTrait;

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

    public function __construct(
        TranslationHelperService $translationHelperService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation,
        private DateHelperService $dateHelperService
    ) {
        parent::__construct($translationHelperService, $formHelper, $flashMessengerHelperService, $navigation);
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
        /* @var $request \Laminas\Http\Request */
        $request = $this->getRequest();

        $eomDate = $this->dateHelperService->getDate('Y-m-t');
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
