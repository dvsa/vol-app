<?php

/**
 * Bus Processing Task controller
 * Bus task search and display
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Bus\Processing;

use Olcs\Controller\Traits;

/**
 * Bus Processing Task controller
 * Bus task search and display
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BusProcessingTaskController extends BusProcessingController
{
    use Traits\TaskSearchTrait;
    use Traits\ListDataTrait;

    protected $identifierName = 'id';
    protected $item = 'tasks';
    protected $service = 'Task';

    /**
     * Render the tasks list or redirect if processing
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $redirect = $this->processTasksActions('busReg');
        if ($redirect) {
            return $redirect;
        }

        $licenceId = $this->getFromRoute('licence');

        $filters = $this->mapTaskFilters(
            array(
                'licenceId'      => $licenceId,
                'assignedToTeam' => '',
                'assignedToUser' => '',
            )
        );

        $table = $this->getTaskTable($filters, false);
        $table->removeColumn('name');
        $table->removeColumn('link');

        $this->setTableFilters($this->getTaskForm($filters));

        $this->loadScripts(['tasks', 'table-actions']);

        $view = $this->getView(['table' => $table]);
        $view->setTemplate('partials/table');

        return $this->renderView($view);
    }
}
