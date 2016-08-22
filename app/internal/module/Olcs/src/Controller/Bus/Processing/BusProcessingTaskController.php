<?php

namespace Olcs\Controller\Bus\Processing;

use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits;

/**
 * Bus Processing Task controller
 * Bus task search and display
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BusProcessingTaskController extends AbstractController implements BusRegControllerInterface, LeftViewProvider
{
    use Traits\ProcessingControllerTrait,
        Traits\TaskActionTrait;

    /**
     * Get task action type
     *
     * @see Olcs\Controller\Traits\TaskActionTrait
     * @return string
     */
    protected function getTaskActionType()
    {
        return 'busReg';
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
            'licence' => $this->getFromRoute('licence'),
            'assignedToTeam' => '',
            'assignedToUser' => '',
        ];
    }
}
