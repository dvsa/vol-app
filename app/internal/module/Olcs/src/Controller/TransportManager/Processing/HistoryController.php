<?php
/**
 * History Controller
 */
namespace Olcs\Controller\TransportManager\Processing;

use Dvsa\Olcs\Transfer\Query\Processing\History;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Common\Controller\Traits as CommonTraits;
use Zend\Mvc\MvcEvent as MvcEvent;

/**
 * History Controller
 */
class HistoryController extends AbstractInternalController implements
    TransportManagerControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'transport_manager_processing_event-history';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = ['transportManager'];
    protected $defaultTableSortField = 'eventDatetime';
    protected $tableName = 'event-history';
    protected $listDto = History::class;

    public function getPageLayout()
    {
        return 'layout/transport-manager-section-crud';
    }

    public function getPageInnerLayout()
    {
        return 'layout/transport-manager-subsection';
    }
}
