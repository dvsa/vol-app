<?php

namespace Olcs\Controller\Operator\Processing;

use Dvsa\Olcs\Transfer\Query\Audit\ReadOrganisation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\OperatorControllerInterface;

class ReadHistoryController extends AbstractInternalController implements OperatorControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'operator_processing_read_history';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = ['id' => 'organisation'];
    protected $tableName = 'read-history';
    protected $listDto = ReadOrganisation::class;

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }
}
