<?php

namespace Olcs\Controller\Bus\Processing;

use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits;

/**
 * Bus Processing Task controller
 * Bus task search and display
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BusProcessingTaskController extends AbstractController implements BusRegControllerInterface, LeftViewProvider
{
    use Traits\ProcessingControllerTrait,
        Traits\TaskSearchTrait;

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

        $filters = $this->mapTaskFilters(
            [
                'licence' => $this->getFromRoute('licence'),
                'assignedToTeam' => '',
                'assignedToUser' => '',
            ]
        );

        $table = $this->getTaskTable($filters);
        $table->removeColumn('name');
        $table->removeColumn('link');

        $this->setTableFilters($this->getTaskForm($filters));

        $this->loadScripts(['tasks', 'table-actions', 'forms/filter']);

        $view = $this->getView(['table' => $table]);
        $view->setTemplate('pages/table');

        return $this->renderView($view);
    }
}
