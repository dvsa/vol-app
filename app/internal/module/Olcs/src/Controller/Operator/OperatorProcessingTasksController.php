<?php

namespace Olcs\Controller\Operator;

use Olcs\Controller\Traits;

/**
 * Operator Processing Tasks Controller
 */
class OperatorProcessingTasksController extends OperatorController
{
    use Traits\TaskActionTrait;

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
     * @see Olcs\Controller\Traits\TaskActionTrait
     * @return string
     */
    protected function getTaskActionType()
    {
        return 'organisation';
    }

    /**
     * Get task action filters
     *
     * @see Olcs\Controller\Traits\TaskActionTrait
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
}
