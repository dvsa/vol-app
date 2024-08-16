<?php

namespace Olcs\Controller\Licence\Processing;

use Dvsa\Olcs\Transfer\Query\Audit\ReadLicence;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;

class ReadHistoryController extends AbstractInternalController implements LicenceControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_processing_read-history';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = ['id' => 'licence'];
    protected $tableName = 'read-history';
    protected $listDto = ReadLicence::class;

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
