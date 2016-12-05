<?php

namespace Olcs\Controller\Bus\Processing;

use Dvsa\Olcs\Utils\Constants\FilterOptions;
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
        Traits\TaskActionTrait {
            Traits\TaskActionTrait::getTaskForm as traitGetTaskForm;
        }

    /**
     * Get task action type
     *
     * @see \Olcs\Controller\Traits\TaskActionTrait
     * 
     * @return string
     */
    protected function getTaskActionType()
    {
        return 'busReg';
    }

    /**
     * Get task action filters
     *
     * @see \Olcs\Controller\Traits\TaskActionTrait
     *
     * @return array
     */
    protected function getTaskActionFilters()
    {
        return [
            'licence' => $this->getFromRoute('licence'),
            'assignedToTeam' => '',
            'assignedToUser' => '',
            'busReg' => $this->getFromRoute('busRegId'),
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
        $form = $this->traitGetTaskForm($filters);

        /** @var \Zend\Form\Element\Select $option */
        $this->updateSelectValueOptions(
            $form->get('showTasks'),
            [
                FilterOptions::SHOW_SELF_ONLY => 'documents.filter.option.this-reg-only',
            ]
        );

        return $form;
    }
}
