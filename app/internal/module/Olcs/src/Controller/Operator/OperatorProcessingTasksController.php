<?php

namespace Olcs\Controller\Operator;

use Olcs\Controller\Traits;

/**
 * Operator Processing Tasks Controller
 */
class OperatorProcessingTasksController extends OperatorController
{
    use Traits\TaskActionTrait {
        Traits\TaskActionTrait::getTaskForm as trait_getTaskForm;
    }

    /**
     * @var string
     */
    protected $section = 'tasks';

    /**
     * @var string
     */
    protected $subNavRoute = 'operator_processing';

    /**
     * Get task action type
     *
     * @see \Olcs\Controller\Traits\TaskActionTrait
     * @return string
     */
    protected function getTaskActionType()
    {
        return 'organisation';
    }

    /**
     * Get task action filters
     *
     * @see \Olcs\Controller\Traits\TaskActionTrait
     * @return array
     */
    protected function getTaskActionFilters()
    {
        return [
            'organisation' => $this->params()->fromRoute('organisation'),
            'assignedToTeam' => '',
            'assignedToUser' => ''
        ];
    }

    /**
     * Create filter form
     *
     * @param array $filters Field values
     *
     * @return \Zend\Form\FormInterface
     */
    protected function getTaskForm(array $filters = [])
    {
        return $this->trait_getTaskForm($filters)
            ->remove('showTasks');
    }
}
