<?php

namespace Olcs\Controller\Traits;

/**
 * Class TaskSearchTrait
 * @package Olcs\Controller
 */
trait TaskSearchTrait
{

    /**
     * Inspect the request to see if we have any filters set, and
     * if necessary, filter them down to a valid subset
     *
     * @return array
     */
    protected function mapTaskFilters($extra = array())
    {
        $defaults = array(
            'owner'  => $this->getLoggedInUser(),
            'team'   => 2,  // we've no stub for this, but it matches the logged in user's team
            'date'   => 'today',
            'status' => 'open',
            'sort'   => 'actionDate',
            'order'  => 'ASC',
            'page'   => 1,
            'limit'  => 10
        );

        $filters = array_merge(
            $defaults,
            $extra,
            $this->getRequest()->getQuery()->toArray()
        );

        // form => backend mappings
        $filters['isClosed'] = $filters['status'] === 'closed';
        $filters['isUrgent'] = isset($filters['urgent']);

        if (isset($filters['date']) && $filters['date'] === 'today') {
            $filters['actionDate'] = '<= ' . date('Y-m-d');
        }

        // nuke any empty values too
        return array_filter(
            $filters,
            function ($v) {
                return !empty($v);
            }
        );
    }

    protected function getTaskForm($filters = array())
    {
        $form = $this->getForm('tasks-home');

        // grab all the relevant backend data needed to populate the
        // various dropdowns on the filter form
        $selects = array(
            'assignedToTeam' => $this->getListData('Team'),
            'assignedToUser' => $this->getListData('User', $filters),
            'category' => $this->getListData('Category', [], 'description'),
            'taskSubCategory' => $this->getListData('TaskSubCategory', $filters)
        );

        // bang the relevant data into the corresponding form inputs
        foreach ($selects as $name => $options) {
            $form->get($name)
                ->setValueOptions($options);
        }

        // setting $this->enableCsrf = false won't sort this; we never POST
        $form->remove('csrf');

        $form->setData($filters);

        return $form;
    }

    protected function getTaskTable($filters = array(), $render = true)
    {
        $tasks = $this->makeRestCall(
            'TaskSearchView',
            'GET',
            $filters
        );

        $table = $this->getTable(
            'tasks',
            $tasks,
            array_merge(
                $filters,
                array('query' => $this->getRequest()->getQuery())
            )
        );

        if ($render) {
            return $table->render();
        }
        return $table;
    }
}
