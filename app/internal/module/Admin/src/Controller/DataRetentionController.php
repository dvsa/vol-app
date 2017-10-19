<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\DataRetention as DataRetentionActions;
use Dvsa\Olcs\Transfer\Query\DataRetention\Records as RecordsListDto;
use Dvsa\Olcs\Transfer\Query\DataRetention\RuleList as ListDto;
use Admin\Form\Model\Form\DelayItem as DelayItemForm;
use Admin\Form\Model\Form\DataRetentionAssign as AssignItemForm;
use Dvsa\Olcs\Transfer\Query\DataRetention\GetRule;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\AbstractInternalController;
use Olcs\Data\Mapper\DelayItems;
use Admin\Data\Mapper\DataRetentionAssign as AssignItemMapper;
use Zend\View\Model\ViewModel;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

/**
 * Data retention controller
 */
class DataRetentionController extends AbstractInternalController implements LeftViewProvider
{
    protected $itemsDelayedSuccessMessage = 'Record(s) are now updated';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-data-retention';

    protected $recordsTableName = 'admin-data-retention-records';

    protected $defaultTableSortField = 'id';
    protected $defaultTableOrderField = 'DESC';

    protected $listDto = ListDto::class;
    protected $recordsListDto = RecordsListDto::class;

    protected $tableName = 'admin-data-retention-rules';
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table';

    protected $deleteParams = ['ids' => 'id', 'status' => 'action'];
    protected $deleteCommand = DataRetentionActions\MarkForDelete::class;
    protected $deleteModalTitle = 'Mark to delete data retention record(s)';
    protected $deleteConfirmMessage = 'Are you sure you want to mark the following for deletion(s)?';
    protected $deleteSuccessMessage = 'Data retention record(s) deleted';

    protected $itemParams = ['ids' => 'id'];

    protected $hasMultiDelete = true;

    protected $redirectConfig = [
        'delete' => [
            'action' => 'records',
            'routeMap' => [
                'dataRetentionRuleId' => 'dataRetentionRuleId',
            ],
            'reUseParams' => true
        ],
        'review' => [
            'action' => 'records',
            'routeMap' => [
                'dataRetentionRuleId' => 'dataRetentionRuleId',
            ],
            'reUseParams' => true
        ],
        'delay' => [
            'action' => 'records',
            'routeMap' => [
                'dataRetentionRuleId' => 'dataRetentionRuleId',
            ],
            'reUseParams' => true
        ],
        'assign' => [
            'action' => 'records',
            'routeMap' => [
                'dataRetentionRuleId' => 'dataRetentionRuleId',
            ],
            'reUseParams' => true
        ],
    ];

    protected $crudConfig = [
        'review' => ['requireRows' => true],
        'delay' => ['requireRows' => true],
        'assign' => ['requireRows' => true],
    ];

    protected $inlineScripts = [
        'recordsAction' => ['table-actions'],
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
                'navigationId' => 'admin-dashboard/admin-data-retention',
                'navigationTitle' => 'Data retention'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Data retention rules');

        return parent::indexAction();
    }

    /**
     * assign action
     *
     * @return ViewModel
     */
    public function assignAction()
    {
        return $this->add(
            AssignItemForm::class,
            new AddFormDefaultData(['ids' => explode(',', $this->params()->fromRoute('id'))]),
            DataRetentionActions\AssignItems::class,
            AssignItemMapper::class,
            'pages/crud-form',
            'Updated record(s)',
            'Assign selected items'
        );
    }

    /**
     * Delay update action
     *
     * @todo this is a bit rubbish, should be able to work the same way as the assign action
     *
     * @return ViewModel
     */
    public function delayAction()
    {
        $formClass = DelayItemForm::class;
        $mapperClass = new DelayItems();
        $updateCommand = new DataRetentionActions\DelayItems();

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $action = ucfirst($this->params()->fromRoute('action'));
        $form = $this->getForm($formClass);
        $this->placeholder()->setPlaceholder('form', $form);
        $this->placeholder()->setPlaceholder('contentTitle', 'Delay selected items');

        if ($request->isPost()) {
            $dataFromPost = (array)$this->params()->fromPost();
            $form->setData($dataFromPost);

            if (method_exists($this, 'alterFormFor' . $action)) {
                $form = $this->{'alterFormFor' . $action}($form, $dataFromPost);
            }
        }

        if ($request->isPost() && $form->isValid()) {
            $commandData = $mapperClass::mapFromForm($form->getData());
            $commandData['ids'] = explode(',', $this->params('id'));

            $response = $this->handleCommand($updateCommand::create($commandData));

            if ($response->isOk()) {
                $this->getServiceLocator()
                    ->get('Helper\FlashMessenger')
                    ->addSuccessMessage($this->itemsDelayedSuccessMessage);

                return $this->redirectTo($response->getResult());
            } elseif ($response->isClientError()) {
                $flashErrors = $mapperClass::mapFromErrors($form, $response->getResult());

                foreach ($flashErrors as $error) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                }

            } elseif ($response->isServerError()) {
                $this->handleErrors($response->getResult());
            }

        }

        return $this->viewBuilder()->buildViewFromTemplate('pages/crud-form');
    }

    /**
     * Review action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function reviewAction()
    {
        $this->deleteModalTitle = 'Mark to review data retention record(s)';
        $this->deleteConfirmMessage = 'Are you sure you want to mark the following for review?';
        $this->deleteSuccessMessage = 'Data retention record(s) status set to review';
        $this->deleteCommand = DataRetentionActions\MarkForReview::class;

        return parent::deleteAction();
    }

    /**
     * Records action
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function recordsAction()
    {
        $ruleId = $this->params('dataRetentionRuleId');
        $query = GetRule::create(['id' => $ruleId]);

        $response = $this->handleQuery($query);
        $dataRetentionRule = $response->getResult();

        $this->placeholder()->setPlaceholder(
            'pageTitle',
            ucwords($dataRetentionRule['description'])
        );

        $this->tableName = $this->recordsTableName;
        $this->listDto = $this->recordsListDto;
        $this->listVars = ['dataRetentionRuleId'];
        $this->defaultTableLimit = 25;

        return parent::indexAction();
    }
}
