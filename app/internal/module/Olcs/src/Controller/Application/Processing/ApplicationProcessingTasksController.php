<?php

namespace Olcs\Controller\Application\Processing;

use Olcs\Controller\Traits;

/**
 * Application Processing Tasks Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingTasksController extends AbstractApplicationProcessingController
{
    use Traits\TaskActionTrait;

    /**
     * @var string
     */
    protected $section = 'tasks';

    /**
     * Get task action type
     *
     * @see Olcs\Controller\Traits\TaskActionTrait
     * @return string
     */
    protected function getTaskActionType()
    {
        return 'application';
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
            'licence' => $this->getLicenceIdForApplication(
                $this->params('application')
            ),
            'assignedToTeam' => '',
            'assignedToUser' => ''
        ];
    }
}
