<?php

/**
 * Companies House Alert Controller
 */
namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Query\CompaniesHouse\AlertList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Form\Model\Form\CompaniesHouseAlertFilters as FilterForm;
use Olcs\Logging\Log\Logger;
use Zend\View\Model\ViewModel;

/**
 * Companies House Alert Controller
 */
class IrhpPermitsController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-permits';


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
    protected $tableViewTemplate = 'pages/table';
    protected $tableName = 'admin-irhp-permits';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDto::class;
    protected $filterForm = FilterForm::class;
    protected $itemParams = ['id'];
    protected $redirectConfig = [
        'close' => [
            'action' => 'index'
        ]
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

    /**
     * Companies house alert list view
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Permits');
        $view = new ViewModel();
        $view->setTemplate('pages/permits/IrhpPermits.phtml');

        return $view;
    }
}
