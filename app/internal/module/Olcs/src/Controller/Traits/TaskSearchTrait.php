<?php

namespace Olcs\Controller\Traits;

use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Task\TaskDetails;
use Dvsa\Olcs\Transfer\Query\Task\TaskList;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Laminas\Form\Element\Select;

/**
 * Task Search Trait
 */
trait TaskSearchTrait
{
    /**
     * Inspect the request to see if we have any filters set, and if necessary, filter them down to a valid subset
     *
     * @param array $extra Extra filters
     *
     * @return array
     */
    protected function mapTaskFilters(array $extra = [])
    {
        $user = $this->currentUser()->getUserData();

        $defaults = [
            'assignedToUser' => $user['id'],
            'assignedToTeam' => $user['team']['id'],
            'date'  => 'tdt_today',
            'status' => 'tst_open',
            'sort' => 'urgent,actionDate',
            'order' => 'DESC,ASC',
            'page' => 1,
            'limit' => 10,
            'showTasks' => FilterOptions::SHOW_ALL,
        ];

        $filters = array_merge(
            $defaults,
            $extra,
            $this->getRequest()->getQuery()->toArray()
        );

        // nuke any empty values too
        return array_filter(
            $filters,
            fn($v) => $v === false || !empty($v)
        );
    }

    /**
     * Get task form
     *
     * @param array $filters Filters
     *
     * @return \Laminas\Form\FormInterface
     */
    protected function getTaskForm(array $filters = [])
    {
        $formHelper = $this->formHelper;

        $form = $formHelper->createForm('TasksHome', false);
        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        //  set default values for dropdowns
        $team = (isset($filters['assignedToTeam'])) ? (int) $filters['assignedToTeam'] : null;
        $category = (isset($filters['category'])) ? (int) $filters['category'] : null;

        // grab all the relevant backend data needed to populate the
        // various dropdowns on the filter form
        $this->subCategoryDataService
            ->setCategory($category);

        $selects = [
            'assignedToTeam' => $this->getListDataTeam('All'),
            'assignedToUser' => $this->getListDataUser($team, 'All'),
        ];

        // bang the relevant data into the corresponding form inputs
        foreach ($selects as $name => $options) {
            $form->get($name)->setValueOptions($options);
        }

        //  show task fields
        /**
        * @var \Laminas\Form\Element\Select $option
        */
        $option = $form->get('showTasks');
        $option->setValueOptions(
            [
                FilterOptions::SHOW_ALL => 'documents.filter.option.all-tasks',
            ]
        );

        $form->setData($filters);

        return $form;
    }

    /**
     * Get task table
     *
     * @param array $filters  Filters
     * @param bool  $noCreate Whether to create table without Create option
     *
     * @return \Common\Service\Table\TableBuilder
     */
    protected function getTaskTable($filters = [], $noCreate = false)
    {
        $response = $this->handleQuery(TaskList::create($filters));
        $tasks = $response->getResult();

        $options = array_merge($filters, ['query' => $this->getRequest()->getQuery()]);
        $tableName = 'tasks' . ($noCreate ? '-no-create' : '');

        $table = $this->getTable($tableName, $tasks, $options);
        $this->updateTableActionWithQuery($table);
        return $table;
    }

    /**
     * Hold processing of task actions
     *
     * @param string $type Type
     *
     * @return bool|\Laminas\Http\Response
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
                case 'organisation':
                    $params = [
                    'type' => 'organisation',
                    'typeId' => $this->params('organisation'),
                    ];
                    break;
                case 'licence':
                    $params = [
                    'type' => 'licence',
                    'typeId' => $this->params('licence'),
                    ];
                    break;
                case 'application':
                    $params = [
                    'type' => 'application',
                    'typeId' => $this->params('application'),
                    ];
                    break;
                case 'transportManager':
                    $params = [
                    'type' => 'tm',
                    'typeId' => $this->params('transportManager'),
                    ];
                    break;
                case 'busReg':
                    $params = [
                    'type' => 'busreg',
                    'typeId' => $this->params('busRegId'),
                    ];
                    break;
                case 'case':
                    $params = [
                    'type' => 'case',
                    'typeId' => $this->params('case'),
                    ];
                    break;
                case 'irhpapplication':
                    $params = [
                    'type' => 'irhpapplication',
                    'typeId' => $this->params('irhpAppId'),
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
            $options = ['query' => $this->getRequest()->getQuery()->toArray()];
            return $this->redirect()->toRoute('task_action', $params, $options);
        }

        return false;
    }

    /**
     * Get task details
     *
     * @param int $id Id
     *
     * @return array
     */
    protected function getTaskDetails($id = null)
    {
        if (!$id) {
            $id = $this->params('task');
        }

        /**
 * @var \Common\Service\Cqrs\Response $response
*/
        $response = $this->handleQuery(TaskDetails::create(['id' => $id]));

        return $response->getResult();
    }

    /**
     * Update table action with query
     *
     * @param \Common\Service\Table\TableBuilder $table Table
     *
     * @return void
     */
    protected function updateTableActionWithQuery($table)
    {
        $query = $this->getRequest()->getUri()->getQuery();
        $action = $table->getVariable('action');
        if ($query) {
            $action .= '?' . $query;
            $table->setVariable('action', $action);
        }
    }

    /**
     * Add/Remove Select options
     *
     * @param Select $el      Target element
     * @param array  $options Add/remove options (for remove value should be null)
     *
     * @return void
     */
    protected function updateSelectValueOptions(Select $el, array $options = [])
    {
        $el->setValueOptions(
            array_filter(
                $options + $el->getValueOptions(),
                fn($arg) => $arg !== null
            )
        );
    }
}
