<?php

/**
 * Case Task controller
 * Case task search and display
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Cases\Processing;

use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Dvsa\Olcs\Transfer\Query\Cases\Cases;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

/**
 * Case Task controller
 * Case task search and display
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TaskController extends OlcsController\CrudAbstract implements CaseControllerInterface, LeftViewProvider
{
    use ControllerTraits\TaskSearchTrait,
        ControllerTraits\CaseControllerTrait,
        ControllerTraits\ListDataTrait;

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_processing_tasks';

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }

    /**
     * Render the tasks list or redirect if processing
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $redirect = $this->processTasksActions('case');

        if ($redirect) {
            return $redirect;
        }

        $case = $this->getCase($this->params()->fromRoute('case', null));

        $filters = $this->mapTaskFilters(
            [
                'assignedToTeam' => '',
                'assignedToUser' => '',
            ]
        );

        $tableFilters = array_merge($filters, $this->getIdArrayForCase($case));

        $table = $this->getTaskTable($tableFilters);
        $table->removeColumn('name');
        $table->removeColumn('link');

        $this->setTableFilters($this->getTaskForm($filters));

        $this->loadScripts(['tasks', 'table-actions', 'forms/filter']);

        $view = $this->getView(['table' => $table]);
        $view->setTemplate('pages/table');

        return $this->renderView($view);
    }

    public function getIdArrayForCase($case)
    {
        $filter = [];

        if (!is_null($case['licence'])) {
            $filter['licence'] = $case['licence']['id'];
        }

        if (!is_null($case['transportManager'])) {
            $filter['transportManager'] = $case['transportManager']['id'];
        }

        if (empty($filter)) {
            throw new \RuntimeException('Must be filtered by licence or transportManager');
        }

        return $filter;
    }

    /**
     * @NOTE Tmp override of CaseControllerTrait method until we have a better solution
     *
     * Gets the case by ID.
     *
     * @param integer $id
     * @return array
     */
    public function getCase($id = null)
    {
        if (is_null($id)) {
            $id = $this->params()->fromRoute('case');
        }

        $response = $this->handleQuery(Cases::create(['id' => $id]));

        // @NOTE added for backwards compatibility until we know what we are doing with these objects
        return new \Olcs\Data\Object\Cases($response->getResult());
    }
}
