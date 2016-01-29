<?php
/**
 * History Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Dvsa\Olcs\Transfer\Query\Processing\History;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Common\Controller\Traits as CommonTraits;
use Zend\Mvc\MvcEvent as MvcEvent;

/**
 * History Controller
 */
class HistoryController extends AbstractInternalController implements LicenceControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_processing_event-history';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = ['licence'];
    protected $defaultTableSortField = 'eventDatetime';
    protected $tableName = 'event-history';
    protected $listDto = History::class;

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }
}
