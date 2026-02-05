<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\Permits\PrintPermits as PrintPermitsDto;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrint as ListDto;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintConfirm as ConfirmListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

class IrhpPermitPrintController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-irhp-permit-print';
    protected $defaultTableSortField = 'permitNumber';
    protected $defaultTableOrderField = 'ASC';

    protected $listVars = ['irhpPermitStock' => 'id'];
    protected $listDto = ListDto::class;

    protected $navigationId = 'admin-dashboard/admin-printing/irhp-permits';

    protected $crudConfig = [
        'confirm' => ['requireRows' => true],
        'cancel' => ['requireRows' => false],
        'print' => ['requireRows' => false],
    ];

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['forms/irhp-permit-print'],
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
                'navigationId' => 'admin-dashboard/admin-printing',
                'navigationTitle' => 'Printing'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Set the page title
     *
     * @return void
     */
    private function setPageTitle()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Print IRHP Permits');
    }

    /**
     * Action: index
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $form = $this->getForm('IrhpPermitPrint');

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $options = [];
                if (!empty($data['fields']['irhpPermitRangeType'])) {
                    $options['query'] = ['irhpPermitRangeType' => $data['fields']['irhpPermitRangeType']];
                }

                return $this->redirect()->toRouteAjax(
                    'admin-dashboard/admin-printing/irhp-permits',
                    [
                        'action' => 'list',
                        'id' => $data['fields']['irhpPermitStock']
                    ],
                    $options,
                    true
                );
            }
        }

        $this->setPageTitle();
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->viewBuilder()->buildView($view);
    }

    /**
     * List Action
     *
     * @return ViewModel | \Laminas\Http\Response
     */
    public function listAction()
    {
        $this->setPageTitle();

        return parent::indexAction();
    }

    /**
     * Confirm Action
     *
     * @return ViewModel | \Laminas\Http\Response
     */
    public function confirmAction()
    {
        $this->setPageTitle();

        return $this->index(
            ConfirmListDto::class,
            new AddFormDefaultData(['ids' => explode(',', (string) $this->params()->fromRoute('id'))]),
            $this->tableViewPlaceholderName,
            'admin-irhp-permit-print-confirm',
            $this->tableViewTemplate
        );
    }

    /**
     * Alter table
     *
     * @param \Common\Service\Table\TableBuilder $table table
     * @param array                              $data  data
     *
     * @return \Common\Service\Table\TableBuilder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    protected function alterTable($table, $data)
    {
        if ($table->hasColumn('sequenceNumber')) {
            $rows = $table->getRows();
            array_walk(
                $rows,
                function (&$item, $key) {
                    $item['sequenceNumber'] = $key + 1;
                }
            );
            $table->setRows($rows);
        }
        return $table;
    }

    /**
     * Print Action
     *
     * @return \Laminas\Http\Response
     */
    public function printAction()
    {
        return $this->processCommand(
            new AddFormDefaultData(['ids' => explode(',', (string) $this->params()->fromRoute('id'))]),
            PrintPermitsDto::class,
            'Permits submitted for printing'
        );
    }

    /**
     * Cancel Action
     *
     * @return \Laminas\Http\Response
     */
    public function cancelAction()
    {
         return $this->redirectTo([]);
    }
}
