<?php

/**
 * Read History Controller
 */
namespace Olcs\Controller\Bus\Processing;

use Dvsa\Olcs\Transfer\Query\Audit\ReadBusReg;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

/**
 * Read History Controller
 */
class ReadHistoryController extends AbstractInternalController implements BusRegControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_bus_processing';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = ['id' => 'busRegId'];
    protected $tableName = 'read-history';
    protected $listDto = ReadBusReg::class;

    /**
     * get Left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/bus/partials/left');

        return $view;
    }
}
