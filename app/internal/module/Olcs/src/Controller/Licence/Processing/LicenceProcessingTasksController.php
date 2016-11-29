<?php

namespace Olcs\Controller\Licence\Processing;

use Olcs\Controller\Traits;

/**
 * Licence Processing Tasks Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceProcessingTasksController extends AbstractLicenceProcessingController
{
    use Traits\TaskActionTrait {
        Traits\TaskActionTrait::getTaskForm as trait_getTaskForm;
    }

    /**
     * @var string
     */
    protected $section = 'tasks';

    /**
     * Get task action type
     *
     * @see \Olcs\Controller\Traits\TaskActionTrait
     * @return string
     */
    protected function getTaskActionType()
    {
        return 'licence';
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
        return $this->trait_getTaskForm($filters)
            ->remove('showTasks');
    }
}
