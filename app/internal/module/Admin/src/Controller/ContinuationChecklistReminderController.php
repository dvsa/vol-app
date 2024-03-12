<?php

namespace Admin\Controller;

use Common\Controller\Lva\Traits\CrudActionTrait;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\Queue as QueueCmd;
use Dvsa\Olcs\Transfer\Query\ContinuationDetail\ChecklistReminders as ChecklistRemindersQry;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Model\ViewModel;

class ContinuationChecklistReminderController extends AbstractController
{
    use CrudActionTrait;

    public const TYPE_CONT_CHECKLIST_REMINDER_GENERATE_LETTER = 'que_typ_cont_check_rem_gen_let';

    protected DateHelperService $dateHelper;
    protected FlashMessengerHelperService $flashMessengerHelper;
    protected ScriptFactory $scriptFactory;
    protected FormHelperService $formHelper;
    protected ResponseHelperService $responseHelper;
    protected TableFactory $tableFactory;

    public function __construct(
        Placeholder $placeholder,
        DateHelperService $dateHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        ResponseHelperService $responseHelper,
        TableFactory $tableFactory
    ) {
        parent::__construct($placeholder);
        $this->dateHelper = $dateHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->scriptFactory = $scriptFactory;
        $this->formHelper = $formHelper;
        $this->responseHelper = $responseHelper;
        $this->tableFactory = $tableFactory;
    }

    /**
     * Display a list of Continuation checklist reminders
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = (array) $request->getPost();

            $crudAction = $this->getCrudAction([$data]);
            if ($crudAction !== null) {
                return $this->handleCrudAction($crudAction);
            }
        }

        $nowDate = $this->dateHelper->getDate('Y-m');
        [$year, $month] = explode('-', $nowDate);

        $filterForm = $this->getChecklistReminderFilterForm($month, $year);
        if ($filterForm->isValid()) {
            [$year, $month] = explode('-', $filterForm->getData()['filters']['date']);
        }

        $response = $this->handleQuery(
            ChecklistRemindersQry::create(
                [
                    'month' => $month,
                    'year' => $year
                ]
            )
        );
        if ($response->isServerError() || $response->isClientError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }
        $results = [];
        $total = 0;
        if ($response->isOk()) {
            $results = $response->getResult()['results'];
            $total = $response->getResult()['count'];
        }

        $table = $this->getTable($results);
        $subTitle = date('M Y', strtotime($year . '-' . $month . '-01'));
        $table->setVariable('title', $subTitle . ': ' . $total . ' licence(s)');

        $this->scriptFactory->loadFiles(['forms/filter', 'forms/crud-table-handler']);

        $view = new ViewModel(['table' => $table, 'filterForm' => $filterForm]);
        $view->setTemplate('pages/table');

        $this->placeholder->getContainer('tableFilters')->set($filterForm);
        $this->setNavigationId('admin-dashboard/continuations');

        return $this->renderView($view, 'admin-generate-continuation-details-title');
    }

    /**
     * Get the filter form
     *
     * @param int $defaultMonth Default month
     * @param int $defaultYear  Default year
     *
     * @return \Laminas\Form\Form
     */
    protected function getChecklistReminderFilterForm($defaultMonth, $defaultYear)
    {
        $queryData = (array) $this->params()->fromQuery('filters');
        $defaults = [
            'date' => [
                'month' => $defaultMonth,
                'year' => $defaultYear,
            ]
        ];

        $filters = array_merge($defaults, $queryData);

        $formHelper = $this->formHelper;
        $form = $formHelper->createForm('ChecklistReminderFilter', false)
            ->setData(['filters' => $filters]);

        if (empty($queryData)) {
            $formHelper->restoreFormState($form);
        } else {
            $formHelper->saveFormState($form, ['filters' => $filters]);
        }

        return $form;
    }

    /**
     * Generate Continuation checklist reminder letters
     *
     * @return \Laminas\Http\Response
     */
    public function generateLettersAction()
    {
        $continuationDetailIds = explode(',', $this->params('child_id'));

        $response = $this->handleCommand(
            QueueCmd::create(
                [
                    'ids' => $continuationDetailIds,
                    'type' => self::TYPE_CONT_CHECKLIST_REMINDER_GENERATE_LETTER
                ]
            )
        );
        $flashMessenger = $this->flashMessengerHelper;
        if ($response->isClientError() || $response->isServerError()) {
            $flashMessenger->addErrorMessage('The checklist reminder letters could not be generated, please try again');
        }

        if ($response->isOk()) {
            $flashMessenger->addSuccessMessage('The checklist reminder letters have been generated.');
        }

        return $this->redirect()->toRouteAjax(null, ['action' => null, 'child_id' => null], [], true);
    }

    /**
     * Export table as CSV action
     *
     * @return \Laminas\Http\Response
     */
    public function exportAction()
    {
        $continuationDetailIds = explode(',', $this->params('child_id'));

        $response = $this->handleQuery(
            ChecklistRemindersQry::create(['ids' => $continuationDetailIds])
        );
        if ($response->isServerError() || $response->isClientError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
            return $this->redirect()->toRouteAjax(null, ['action' => null, 'child_id' => null], [], true);
        }
        $results = [];
        if ($response->isOk()) {
            $results = $response->getResult()['results'];
        }

        $table = $this->getTable($results);

        $helper = $this->responseHelper;
        return $helper->tableToCsv($this->getResponse(), $table, 'Checklist reminder list');
    }

    /**
     * Get the table with populated data
     *
     * @param array $results Array of entity data
     *
     * @return \Common\Service\Table\TableBuilder
     */
    protected function getTable($results)
    {
        $table = $this->tableFactory->prepareTable('admin-continuations-checklist', $results);

        return $table;
    }
}
