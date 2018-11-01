<?php

namespace Admin\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Dvsa\Olcs\Transfer\Command\Permits\PrintPermits as PrintPermitsDto;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrint as ListDto;

use Zend\View\Model\ViewModel;

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

    protected $navigationId = 'admin-dashboard/admin-printing/irhp-permits';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    protected $crudConfig = [
        'print' => ['requireRows' => true],
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
}
