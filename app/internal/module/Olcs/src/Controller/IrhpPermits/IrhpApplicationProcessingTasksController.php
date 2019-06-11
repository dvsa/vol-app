<?php

namespace Olcs\Controller\IrhpPermits;

use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Controller\Traits;

/**
 * Irhp Application Processing Tasks Controller
 */
class IrhpApplicationProcessingTasksController extends AbstractIrhpPermitProcessingController implements
    IrhpApplicationControllerInterface
{
    use Traits\TaskActionTrait {
        Traits\TaskActionTrait::getTaskForm as traitGetTaskForm;
    }

    use ShowIrhpApplicationNavigationTrait;

    /**
     * Get task action type
     *
     * @see \Olcs\Controller\Traits\TaskActionTrait
     * @return string
     */
    protected function getTaskActionType()
    {
        return 'irhpapplication';
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
            'licence' => $this->getFromRoute('licence'),
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
        return $this->traitGetTaskForm($filters)
            ->remove('showTasks');
    }
}
