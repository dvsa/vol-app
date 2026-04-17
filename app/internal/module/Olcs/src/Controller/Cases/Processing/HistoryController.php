<?php

namespace Olcs\Controller\Cases\Processing;

use Olcs\Controller\AbstractHistoryController;
use Olcs\Controller\Interfaces\CaseControllerInterface;

class HistoryController extends AbstractHistoryController implements CaseControllerInterface
{
    protected $itemParams = ['case', 'id' => 'id'];
    protected $navigationId = 'case_processing_history';
    protected $listVars = ['case'];

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
