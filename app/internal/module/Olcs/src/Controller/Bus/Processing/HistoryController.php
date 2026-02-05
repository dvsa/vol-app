<?php

namespace Olcs\Controller\Bus\Processing;

use Dvsa\Olcs\Transfer\Query\Bus\HistoryList as BusRegHistoryList;
use Olcs\Controller\AbstractHistoryController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;

class HistoryController extends AbstractHistoryController implements BusRegControllerInterface
{
    protected $navigationId = 'licence_bus_processing_event-history';
    protected $listVars = ['busReg' => 'busRegId'];
    protected $listDto = BusRegHistoryList::class;
    protected $itemParams = ['busReg', 'id' => 'id'];

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
