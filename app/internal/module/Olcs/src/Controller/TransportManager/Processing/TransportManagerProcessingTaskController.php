<?php

/**
 * Transport Manager Processing Task Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\TransportManager\Processing;

use Olcs\Controller\Traits\TaskSearchTrait;

/**
 * Transport Manager Processing Task Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransportManagerProcessingTaskController extends AbstractTransportManagerProcessingController
{
    use TaskSearchTrait;

    /**
     * Render the tasks list or redirect if processing
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $redirect = $this->processTasksActions('transportManager');

        if ($redirect) {
            return $redirect;
        }

        $transportManagerId = $this->getFromRoute('transportManager');
        $filters = $this->mapTaskFilters(
            [
                'transportManager' => $transportManagerId,
                'assignedToTeam' => '',
                'assignedToUser' => '',
            ]
        );

        $table = $this->getTaskTable($filters);
        $table->removeColumn('name');
        $table->removeColumn('link');

        $this->setTableFilters($this->getTaskForm($filters));

        $this->loadScripts(['tasks', 'table-actions', 'forms/filter']);

        $view = $this->getViewWithTm(['table' => $table]);
        $view->setTemplate('pages/table');

        return $this->renderView($view);
    }
}
