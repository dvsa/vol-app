<?php
/**
 * History Controller
 */
namespace Olcs\Controller\Bus\Processing;

use Dvsa\Olcs\Transfer\Query\Bus\HistoryList as BusRegHistoryList;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Common\Controller\Traits as CommonTraits;
use Zend\Mvc\MvcEvent as MvcEvent;

/**
 * History Controller
 */
class HistoryController extends AbstractInternalController implements BusRegControllerInterface, LeftViewProvider
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
    protected $listVars = ['busReg' => 'busRegId'];
    protected $defaultTableSortField = 'eventDatetime';
    protected $tableName = 'event-history';
    protected $listDto = BusRegHistoryList::class;

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/bus/partials/left');

        return $view;
    }
}
