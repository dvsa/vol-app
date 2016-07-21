<?php
/**
 * Application History Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Processing;

use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\AbstractHistoryController;

/**
 * Application History Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class HistoryController extends AbstractHistoryController implements CaseControllerInterface
{
    protected $itemParams = ['case', 'id' => 'id'];
    protected $navigationId = 'case_processing_history';
    protected $listVars = ['case'];

    /**
     * Alter table
     *
     * @param \Common\Service\Table\TableBuilder $table table
     * @param array                              $data data
     *
     * @return \Common\Service\Table\TableBuilder
     */
    protected function alterTable($table, $data)
    {
        $table->removeColumn('appId');
        return $table;
    }
}
