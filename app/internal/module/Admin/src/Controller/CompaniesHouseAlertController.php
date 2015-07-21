<?php

/**
 * Partner Controller
 */
namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Query\CompaniesHouse\AlertList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Data\Mapper\Partner as Mapper;
use Admin\Form\Model\Form\Partner as Form;
use Olcs\Form\Model\Form\CompaniesHouseAlertFilters as FilterForm;

/**
 * Partner Controller
 */
class CompaniesHouseAlertController extends AbstractInternalController implements
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-report';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions', 'forms/filter'],
    ];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $tableName = 'admin-companies-house-alerts';
    protected $listDto = ListDto::class;
    protected $filterForm = FilterForm::class;

    public function getPageLayout()
    {
        return 'layout/admin-report-section';
    }

    public function getPageInnerLayout()
    {
        return 'layout/wide-layout';
    }

    private function setPageTitle()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Companies House change alerts');
    }

    public function indexAction()
    {
        $this->setPageTitle();

        $view = parent::indexAction();

        // populate the filter dropdown from the data retrieved by the main ListDto
        $valueOptions = $this->listData['extra']['valueOptions']['companiesHouseAlertReason'];
        $this->getServiceLocator()
            ->get('viewHelperManager')
            ->get('placeholder')
            ->getContainer('tableFilters')
            ->getValue()
            ->get('typeOfChange')
            ->setValueOptions($valueOptions)
            ->setEmptyOption('ch_alert_reason.all');

        return $view;
    }

    public function detailsAction()
    {
        return $this->notFoundAction();
    }
}
