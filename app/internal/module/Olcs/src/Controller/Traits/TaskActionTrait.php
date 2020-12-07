<?php

namespace Olcs\Controller\Traits;

use Laminas\View\Model\ViewModel;

/**
 * Task Action Trait
 */
trait TaskActionTrait
{
    use TaskSearchTrait;

    /**
     * Get task action type
     *
     * @return string
     */
    abstract protected function getTaskActionType();

    /**
     * Get task action filters
     *
     * @return array
     */
    abstract protected function getTaskActionFilters();

    /**
     * Render the tasks list or redirect if processing
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        $redirect = $this->processTasksActions($this->getTaskActionType());

        if ($redirect) {
            return $redirect;
        }

        $filters = $this->mapTaskFilters(
            $this->getTaskActionFilters()
        );

        $table = $this->getTaskTable($filters);
        $table->removeColumn('name');
        $table->removeColumn('link');

        $this->setTableFilters($this->getTaskForm($filters));

        $this->loadScripts(['tasks', 'table-actions', 'forms/filter']);

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('pages/table');

        return $this->renderView($view);
    }
}
