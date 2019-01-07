<?php

namespace Admin\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Dvsa\Olcs\Transfer\Command\Permits\PrintPermits as PrintPermitsDto;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrint as ListDto;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintConfirm as ConfirmListDto;
use Zend\View\Model\ViewModel;
use Admin\Form\Model\Form\IrhpPermitPrintFilter as FilterForm;

/**
 * IRHP Permits Stock Print Controller
 */
class IrhpPermitPrintController extends AbstractInternalController implements LeftViewProvider, ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::ADMIN_PERMITS
        ],
    ];

    protected $tableName = 'admin-irhp-permit-print';
    protected $defaultTableSortField = 'permitNumber';
    protected $defaultTableOrderField = 'ASC';

    protected $listVars = [];
    protected $listDto = ListDto::class;
    protected $filterForm = FilterForm::class;

    protected $navigationId = 'admin-dashboard/admin-printing/irhp-permits';

    protected $crudConfig = [
        'confirm' => ['requireRows' => true],
        'cancel' => ['requireRows' => false],
        'print' => ['requireRows' => false],
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
     * Confirm Action
     *
     * @return ViewModel | \Zend\Http\Response
     */
    public function confirmAction()
    {
        return $this->index(
            ConfirmListDto::class,
            new AddFormDefaultData(['ids' => explode(',', $this->params()->fromRoute('id'))]),
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
     */
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
     * @return \Zend\Http\Response
     */
    public function printAction()
    {
        return $this->processCommand(
            new AddFormDefaultData(['ids' => explode(',', $this->params()->fromRoute('id'))]),
            PrintPermitsDto::class,
            'Permits submitted for printing'
        );
    }

    /**
     * Cancel Action
     *
     * @return \Zend\Http\Response
     */
    public function cancelAction()
    {
         return $this->redirectTo([]);
    }
}
