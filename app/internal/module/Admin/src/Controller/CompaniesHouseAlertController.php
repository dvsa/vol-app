<?php

/**
 * Companies House Alert Controller
 */
namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\CompaniesHouse\CloseAlerts as CloseDto;
use Dvsa\Olcs\Transfer\Query\CompaniesHouse\AlertList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Form\Model\Form\CompaniesHouseAlertFilters as FilterForm;
use Olcs\Logging\Log\Logger;
use Zend\View\Model\ViewModel;

/**
 * Companies House Alert Controller
 */
class CompaniesHouseAlertController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-report';

    protected $crudConfig = [
        'close' => ['requireRows' => true],
    ];

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
    protected $defaultTableSortField = 'companyOrLlpNo';
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
                'navigationId' => 'admin-dashboard/admin-report',
                'navigationTitle' => 'Reports'
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
        $this->placeholder()->setPlaceholder('pageTitle', 'Companies House alerts');

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

    /**
     * Close action
     *
     * @return ViewModel
     */
    public function closeAction()
    {
        Logger::debug(__FILE__);
        Logger::debug(__METHOD__);

        $confirmMessage = $this->getServiceLocator()->get('Helper\Translation')
            ->translate('companies-house-alert.close.confirm');
        $confirm = $this->confirm($confirmMessage);

        if ($confirm instanceof ViewModel) {
            $this->placeholder()->setPlaceholder('pageTitle', 'companies-house-alert.close.title');
            return $this->viewBuilder()->buildView($confirm);
        }

        $dtoData = ['ids' => explode(',', $this->params()->fromRoute('id'))];
        $response = $this->handleCommand(CloseDto::create($dtoData));

        if ($response->isServerError() || $response->isClientError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addSuccessMessage('companies-house-alert.close.success');
        }

        return $this->redirectTo($response->getResult());
    }
}
