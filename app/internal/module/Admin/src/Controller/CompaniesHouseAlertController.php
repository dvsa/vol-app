<?php

namespace Admin\Controller;

use Admin\Controller\Traits\ReportLeftViewTrait;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\CompaniesHouse\CloseAlerts as CloseDto;
use Dvsa\Olcs\Transfer\Query\CompaniesHouse\AlertList as ListDto;
use Laminas\Navigation\Navigation;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Form\Model\Form\CompaniesHouseAlertFilters as FilterForm;
use Olcs\Logging\Log\Logger;

class CompaniesHouseAlertController extends AbstractInternalController implements LeftViewProvider
{
    use ReportLeftViewTrait;

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

    public function __construct(
        TranslationHelperService $translationHelperService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation,
        protected HelperPluginManager $viewHelperPluginManager
    ) {
        parent::__construct($translationHelperService, $formHelper, $flashMessengerHelperService, $navigation);
    }
    /**
     * Companies house alert list view
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Companies House alerts');

        $view = parent::indexAction();

        // populate the filter dropdown from the data retrieved by the main ListDto
        $valueOptions = $this->listData['extra']['valueOptions']['companiesHouseAlertReason'];
        $this->viewHelperPluginManager
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

        $confirmMessage = $this->translationHelperService
            ->translate('companies-house-alert.close.confirm');
        $confirm = $this->confirm($confirmMessage);

        if ($confirm instanceof ViewModel) {
            $this->placeholder()->setPlaceholder('pageTitle', 'companies-house-alert.close.title');
            return $this->viewBuilder()->buildView($confirm);
        }

        $dtoData = ['ids' => explode(',', $this->params()->fromRoute('id'))];
        $response = $this->handleCommand(CloseDto::create($dtoData));

        if ($response->isServerError() || $response->isClientError()) {
            $this->flashMessengerHelperService->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $this->flashMessengerHelperService
                ->addSuccessMessage('companies-house-alert.close.success');
        }

        return $this->redirectTo($response->getResult());
    }
}
