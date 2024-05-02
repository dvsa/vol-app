<?php

namespace Admin\Controller\DataRetention;

use Admin\Form\Model\Form\DataRetentionExport;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Query\DataRetention\GetProcessedList;
use Dvsa\Olcs\Transfer\Query\DataRetention\RuleList;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\DataRetentionExport as Mapper;

class ExportController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-data-retention';

    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation,
        protected TableFactory $tableFactory,
        protected ResponseHelperService $responseHelperService
    ) {
        parent::__construct($translationHelper, $formHelper, $flashMessengerHelperService, $navigation);
    }
    /**
     * Left View setting
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
     * Process action - Index
     *
     * @return \Laminas\Http\Response
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Export deleted items');

        $form = $this->getForm(DataRetentionExport::class);
        $this->setSelectDataRetentionRuleList($form);

        $this->placeholder()->setPlaceholder('form', $form);

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $dataFromPost = (array) $this->params()->fromPost();
            $form->setData($dataFromPost);
        }

        if ($request->isPost() && $form->isValid()) {
            $commandData = Mapper::mapFromForm($form->getData());
            $response = $this->handleQuery(GetProcessedList::create($commandData));

            if ($response->isOk()) {
                if ($response->getResult()['count'] > 0) {
                    $table = $this->tableFactory->prepareTable(
                        'data-retention-export',
                        $response->getResult()
                    );

                    return $this->responseHelperService
                        ->tableToCsv($this->getResponse(), $table, 'data-retention');
                }
                $this->flashMessengerHelperService->addErrorMessage('No data retention items were found');
            } else {
                $this->flashMessengerHelperService->addUnknownError();
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate('pages/crud-form');
    }

    /**
     * Set the list of data retention rules in the select input
     *
     * @param \Common\Form\Form $form Form
     *
     * @return void
     */
    private function setSelectDataRetentionRuleList(\Common\Form\Form $form)
    {
        $response = $this->handleQuery(
            RuleList::create(
                ['page' => 1, 'limit' => 100, 'sort' => 'description', 'order' => 'ASC', 'isReview' => 'N']
            )
        );
        if ($response->isOk()) {
            $select = $form->get('exportOptions')->get('rule');
            $options = [];
            foreach ($response->getResult()['results'] as $dataRetentionRule) {
                $options[$dataRetentionRule['id']] = $dataRetentionRule['description'];
            }
            /* @var $select \Laminas\Form\Element\Select */
            $select->setValueOptions($options);
        }
    }
}
