<?php

/**
 * Read History Controller
 */
namespace Olcs\Controller\Application\Processing;

use Dvsa\Olcs\Transfer\Query\Audit\ReadApplication;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Zend\View\Model\ViewModel;

/**
 * Read History Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReadHistoryController extends AbstractInternalController implements
    ApplicationControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'application_processing_read_history';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = ['id' => 'application'];
    protected $tableName = 'read-history';
    protected $listDto = ReadApplication::class;

    /**
     * get method LeftView
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }
}
