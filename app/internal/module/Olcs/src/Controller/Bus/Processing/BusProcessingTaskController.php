<?php

/**
 * Bus Processing Task controller
 * Bus task search and display
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Bus\Processing;

use Olcs\Controller\Bus\BusController;
use Olcs\Controller\Traits;

/**
 * Bus Processing Task controller
 * Bus task search and display
 *
 * @NOTE Migrated
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BusProcessingTaskController extends BusController
{
    use Traits\TaskSearchTrait,
        Traits\ListDataTrait;

    protected $identifierName = 'id';
    protected $item = 'tasks';
    protected $service = 'Task';

    protected $section = 'processing';
    protected $subNavRoute = 'licence_bus_processing';

    /**
     * Holds an array of variables for the
     * default index list page.
     */

    protected $listVars = [
        'licence',
        'busRegId'
    ];

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
            [
                'licence' => $licenceId,
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
