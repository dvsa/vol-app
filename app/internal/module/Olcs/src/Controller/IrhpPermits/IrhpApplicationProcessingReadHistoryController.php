<?php

namespace Olcs\Controller\IrhpPermits;

use Dvsa\Olcs\Transfer\Query\Audit\ReadIrhpApplication;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * Irhp Application Processing Read History Controller
 */
class IrhpApplicationProcessingReadHistoryController extends AbstractInternalController implements
    IrhpApplicationControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_irhp_applications_processing_read-history';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = ['id' => 'irhpAppId'];
    protected $tableName = 'read-history';
    protected $listDto = ReadIrhpApplication::class;

    /**
     * get method for left view
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
