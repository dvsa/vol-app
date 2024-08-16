<?php

namespace Olcs\Controller\TransportManager\Processing;

use Dvsa\Olcs\Transfer\Query\Audit\ReadTransportManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;

class ReadHistoryController extends AbstractInternalController implements
    TransportManagerControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'transport_manager_processing_read-history';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = ['id' => 'transportManager'];
    protected $tableName = 'read-history';
    protected $listDto = ReadTransportManager::class;

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/transport-manager/partials/processing-left');

        return $view;
    }
}
