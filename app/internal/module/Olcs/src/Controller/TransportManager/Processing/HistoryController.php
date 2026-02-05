<?php

namespace Olcs\Controller\TransportManager\Processing;

use Olcs\Controller\AbstractHistoryController;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;

class HistoryController extends AbstractHistoryController implements TransportManagerControllerInterface
{
    protected $navigationId = 'transport_manager_processing_event-history';
    protected $listVars = ['transportManager'];
    protected $itemParams = ['transportManager', 'id' => 'id'];

    /**
     * Alter table
     *
     * @param \Common\Service\Table\TableBuilder $table table
     * @param array                              $data  data
     *
     * @return \Common\Service\Table\TableBuilder
     */
    #[\Override]
    protected function alterTable($table, $data)
    {
        $table->removeColumn('appId');
        return $table;
    }
}
