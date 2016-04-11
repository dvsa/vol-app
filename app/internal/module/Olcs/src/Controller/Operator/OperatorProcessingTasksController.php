<?php

/**
 * Operator Processing Tasks Controller
 */
namespace Olcs\Controller\Operator;

use Olcs\Controller\Traits;
use Zend\View\Model\ViewModel;

/**
 * Operator Processing Tasks Controller
 */
class OperatorProcessingTasksController extends OperatorController
{
    use Traits\TaskSearchTrait;
    use Traits\ListDataTrait;

    /**
     * @var string
     */
    protected $section = 'tasks';

    /**
     * @var string
     */
    protected $subNavRoute = 'operator_processing';

    public function indexAction()
    {
        $redirect = $this->processTasksActions('organisation');

        if ($redirect) {
            return $redirect;
        }

        $filters = $this->mapTaskFilters(
            [
                'organisation' => $this->params()->fromRoute('organisation'),
                'assignedToTeam' => '',
                'assignedToUser' => ''
            ]
        );

        $table = $this->getTaskTable($filters);

        // the table's nearly all good except we don't want a couple of columns
        $table->removeColumn('name');
        $table->removeColumn('link');

        $this->setTableFilters($this->getTaskForm($filters));

        $this->loadScripts(['tasks', 'table-actions', 'forms/filter']);

        $view = new ViewModel(['table' => $table]);

        $view->setTemplate('pages/table');

        return $this->renderView($view);
    }
}
