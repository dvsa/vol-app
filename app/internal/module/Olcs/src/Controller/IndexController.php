<?php

namespace Olcs\Controller;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Olcs\Controller\Traits\TaskSearchTrait;

/**
 * Index Controller
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IndexController extends AbstractController implements LeftViewProvider
{
    use TaskSearchTrait;

    /**
     * Process action - Index
     *
     * @return bool|\Zend\Http\Response|ViewModel
     * @throws \Exception
     */
    public function indexAction()
    {
        $redirect = $this->processTasksActions();
        if ($redirect) {
            return $redirect;
        }

        $filters = $this->mapTaskFilters();

        /** @var \Common\Service\Table\TableBuilder $table */
        $table = null;

        // assignedToTeam or Category must be selected
        if (empty($filters['assignedToTeam'])
            && empty($filters['category'])
        ) {
            $table = $this->getTable('tasks-no-create', []);
            $table->setEmptyMessage('tasks.search.error.filter.needed');

            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addWarningMessage('tasks.search.error.filter.needed');

        } else {
            //  if user specified then remove team from filters (ignore team) @see OLCS-13501
            if (!empty($filters['assignedToUser'])) {
                unset($filters['assignedToTeam']);
            }

            $table = $this->getTaskTable($filters, true);

            $this->loadScripts(['tasks', 'table-actions', 'forms/filter']);
        }

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('pages/table');

        return $this->renderView($view, 'Home');
    }

    /**
     * Build left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $filters = $this->mapTaskFilters();

        $left = new ViewModel(['form' => $this->getTaskForm($filters)]);
        $left->setTemplate('sections/home/partials/left');

        return $left;
    }

    /**
     * Retrieve a list of entities, filtered by a certain key.
     * The consumer doesn't control what the entities and keys are; they
     * simply provide a key and a value which we look up in a map
     *
     * @return JsonModel
     */
    public function entityListAction()
    {
        $key = $this->params('type');
        $value = $this->params('value');

        switch ($key) {
            case 'enforcement-area':
                $results = $this->getListDataEnforcementArea($value, 'Please select');
                break;
            case 'task-allocation-users':
                /** @var \Olcs\Service\Data\UserListInternal $srv */
                $srv = $this->getServiceLocator()->get(\Olcs\Service\Data\UserListInternal::class);
                $srv->setTeamId($value);

                $results =
                    [
                        '' => 'Unassigned',
                        'alpha-split' => 'Alpha split',
                    ] +
                    $srv->fetchListOptions(null);

                break;
            case 'users-internal':
                /** @var \Olcs\Service\Data\UserListInternal $srv */
                $srv = $this->getServiceLocator()->get(\Olcs\Service\Data\UserListInternal::class);
                $srv->setTeamId($value);

                $results =
                    [
                        '' => 'Unassigned',
                    ] +
                    $srv->fetchListOptions(null);

                break;

            case 'users':
                $results = $this->getListDataUser($value, 'All');
                break;
            case 'task-sub-categories':
                $results = $this->getListDataSubCategoryTask($value, 'All');
                break;
            case 'document-sub-categories':
                $results = $this->getListDataSubCategoryDocs($value, 'All');
                break;
            case 'sub-categories-no-first-option':
                $results = $this->getListDataSubCategory([], $value, false);
                break;
            case 'sub-categories':
                $results = $this->getListDataSubCategory([], $value, true);
                break;
            case 'scanning-sub-categories':
                $results = $this->getListDataSubCategoryScan($value, 'All');
                break;
            case 'document-templates':
                $results = $this->getListDataDocTemplates(null, $value, 'All');
                break;
            case 'sub-category-descriptions':
                $results = $this->getListDataSubCategoryDescription($value);
                break;
            default:
                throw new \Exception('Invalid entity filter key: ' . $key);
        }

        // iterate over the list data and just convert it to a more
        // JS friendly format (key/val assoc isn't quite such a neat
        // fit for frontend)
        $viewResults = [];
        foreach ($results as $id => $result) {
            $viewResults[] = [
                'value' => $id,
                'label' => $result
            ];
        }

        return new JsonModel($viewResults);
    }
}
