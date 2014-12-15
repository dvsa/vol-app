<?php

namespace Olcs\Controller\Traits;

/**
 * Class TaskSearchTrait
 * @package Olcs\Controller
 */
trait TaskSearchTrait
{

    /**
     * Cache task details to eliminate duplicate REST calls in same request
     *
     * @var array
     */
    protected $taskDetailsCache = [];

    /**
     * Inspect the request to see if we have any filters set, and
     * if necessary, filter them down to a valid subset
     *
     * @return array
     */
    protected function mapTaskFilters($extra = array())
    {
        $defaults = array(
            'assignedToUser' => $this->getLoggedInUser(),
            'assignedToTeam' => 2,  // we've no stub for this, but it matches the logged in user's team
            'date'           => 'tdt_today',
            'status'         => 'tst_open',
            'sort'           => 'actionDate',
            'order'          => 'ASC',
            'page'           => 1,
            'limit'          => 10
        );

        $filters = array_merge(
            $defaults,
            $extra,
            $this->getRequest()->getQuery()->toArray()
        );

        // form => backend mappings

        // we need an if / else if here because there is a third input
        // state, "all", which shouldn't apply either filter
        if ($filters['status'] === 'tst_closed') {
            $filters['isClosed'] = true;
        } elseif ($filters['status'] === 'tst_open') {
            $filters['isClosed'] = false;
        }

        if (isset($filters['date']) && $filters['date'] === 'tdt_today') {
            $filters['actionDate'] = '<= ' . date('Y-m-d');
        }

        // nuke any empty values too
        return array_filter(
            $filters,
            function ($v) {
                return $v === false || !empty($v);
            }
        );
    }

    protected function getTaskForm($filters = array())
    {
        $form = $this->getForm('tasks-home');

        // the filters generally double up perfectly as form
        // and filter data, but team just needs a little bump...
        if (isset($filters['assignedToTeam'])) {
            $filters['team'] = $filters['assignedToTeam'];
        }

        // @see https://jira.i-env.net/browse/OLCS-6061. Don't worry, filters are ignored
        // if the entity doesn't have the relevant field, so it's safe to cram this in here
        $filters['isTask'] = true;

        // grab all the relevant backend data needed to populate the
        // various dropdowns on the filter form
        $selects = array(
            'assignedToTeam' => $this->getListDataFromBackend('Team'),
            'assignedToUser' => $this->getListDataFromBackend('User', $filters),
            'category' => $this->getListDataFromBackend('Category', [], 'description'),
            'taskSubCategory' => $this->getListDataFromBackend('SubCategory', $filters, 'subCategoryName')
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

    protected function getTaskTable($filters = array(), $render = true, $noCreate = false)
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

        if ($noCreate) {
            $settings = $table->getSettings();
            if (isset($settings['crud']['actions']['create task'])) {
                unset($settings['crud']['actions']['create task']);
                if (isset($settings['crud']['actions']['edit']) && is_array($settings['crud']['actions']['edit'])) {
                    $settings['crud']['actions']['edit']['class'] = 'primary';
                }
                $table->setSettings($settings);
            }
        }

        if ($render) {
            return $table->render();
        }
        return $table;
    }

    /**
     * Hold processing of task actions
     *
     * @param string $type
     * @return bool|redirect
     */
    protected function processTasksActions($type = '')
    {
        if ($this->getRequest()->isPost()) {

            $action = strtolower($this->params()->fromPost('action'));
            if ($action === 're-assign task') {
                $action = 'reassign';
            } elseif ($action === 'create task') {
                $action = 'add';
            } elseif ($action === 'close task') {
                $action = 'close';
            }

            if ($action !== 'add') {
                $id = $this->params()->fromPost('id');

                // pass multiple ids to re-assign or close
                if (($action === 'reassign' || $action === 'close') && is_array($id)) {
                    $id = implode('-', $id);
                }

                // we need only one id to edit
                if ($action === 'edit') {
                    if (!is_array($id) || count($id) !== 1) {
                        throw new \Exception('Please select a single task to edit');
                    }
                    $id = $id[0];
                }
            }

            switch ($type) {
                case 'licence':
                    $params = [
                        'type' => 'licence',
                        'typeId' => $this->getFromRoute('licence'),
                    ];
                    break;
                case 'application':
                    $params = [
                        'type' => 'application',
                        'typeId' => $this->getFromRoute('application'),
                    ];
                    break;
                case 'transportManager':
                    $params = [
                        'type' => 'tm',
                        'typeId' => $this->getFromRoute('transportManager'),
                    ];
                    break;
                    break;
                case 'busReg':
                    $params = [
                        'type' => 'busreg',
                        'typeId' => $this->getFromRoute('busRegId'),
                    ];
                    break;
                default:
                    // no type - call from the home page
                    break;
            }
            $params['action'] = $action;

            if ($action !== 'add') {
                $params['task'] = $id;
            }

            return $this->redirect()->toRoute(
                'task_action',
                $params
            );

        }

        return false;
    }

    /**
     * Get task details
     *
     * @param int $id
     * @return array
     */
    protected function getTaskDetails($id = null)
    {
        $taskDetails = array();
        if ($id) {
            if (!isset($this->taskDetailsCache[$id])) {
                $this->taskDetailsCache[$id] = $this->makeRestCall(
                    'TaskSearchView',
                    'GET',
                    array('id' => $id),
                    array('properties' => array('linkType', 'linkId', 'linkDisplay', 'licenceId'))
                );
            }
        }
        return $this->taskDetailsCache[$id];
    }
}
