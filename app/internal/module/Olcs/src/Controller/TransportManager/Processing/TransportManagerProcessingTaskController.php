<?php

namespace Olcs\Controller\TransportManager\Processing;

use Olcs\Controller\Traits;

class TransportManagerProcessingTaskController extends AbstractTransportManagerProcessingController
{
    use Traits\TaskActionTrait {
        Traits\TaskActionTrait::getTaskForm as traitGetTaskForm;
    }

    /**
     * Get task action type
     *
     * @see    \Olcs\Controller\Traits\TaskActionTrait
     * @return string
     */
    protected function getTaskActionType()
    {
        return 'transportManager';
    }

    /**
     * Get task action filters
     *
     * @see    \Olcs\Controller\Traits\TaskActionTrait
     * @return array
     */
    protected function getTaskActionFilters()
    {
        return [
            'transportManager' => $this->getFromRoute('transportManager'),
            'assignedToTeam' => '',
            'assignedToUser' => ''
        ];
    }

    /**
     * Create filter form
     *
     * @param array $filters Field values
     *
     * @return \Laminas\Form\FormInterface
     */
    protected function getTaskForm(array $filters = [])
    {
        return $this->traitGetTaskForm($filters)
            ->remove('showTasks');
    }
}
