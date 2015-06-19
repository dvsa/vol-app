<?php
/**
 * History Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Dvsa\Olcs\Transfer\Query\Processing\History;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
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
    LicenceControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
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

    public function getPageLayout()
    {
        return 'layout/licence-section';
    }

    public function getPageInnerLayout()
    {
        return 'layout/processing-subsection';
    }
}
