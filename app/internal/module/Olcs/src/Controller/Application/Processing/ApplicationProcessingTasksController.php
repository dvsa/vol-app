<?php

/**
 * Application Processing Tasks Controller
 */
namespace Olcs\Controller\Application\Processing;

use Zend\View\Model\ViewModel;
use \Olcs\Controller\Traits\TaskSearchTrait;

/**
 * Application Processing Tasks Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingTasksController extends AbstractApplicationProcessingController
{
    use TaskSearchTrait;

    /**
     * @var string
     */
    protected $section = 'tasks';

    public function indexAction()
    {
        $redirect = $this->processTasksActions('application');

        if ($redirect) {
            return $redirect;
        }

        // we want all tasks related to the licence, not just this application
        $applicationId = $this->params('application');
        $licenceId = $this->getLicenceIdForApplication($applicationId);
        $filters = $this->mapTaskFilters(
            [
                'licence' => $licenceId,
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

        return $this->viewBuilder()->buildView($view);
    }
}
