<?php

namespace Olcs\Controller\Cases\Processing;

use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Task controller
 * Case task search and display
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TaskController extends AbstractController implements CaseControllerInterface, LeftViewProvider
{
    use ControllerTraits\CaseControllerTrait,
        ControllerTraits\ProcessingControllerTrait,
        ControllerTraits\TaskActionTrait {
            ControllerTraits\TaskActionTrait::getTaskForm as trait_getTaskForm;
        }

    /**
     * Get task action type
     *
     * @see \Olcs\Controller\Traits\TaskActionTrait
     * @return string
     */
    protected function getTaskActionType()
    {
        return 'case';
    }

    /**
     * Get task action filters
     *
     * @see \Olcs\Controller\Traits\TaskActionTrait
     * @return array
     */
    protected function getTaskActionFilters()
    {
        return array_merge(
            [
                'assignedToTeam' => '',
                'assignedToUser' => ''
            ],
            $this->getIdArrayForCase()
        );
    }

    /**
     * Get id array for case
     *
     * @return array
     * @throw \RuntimeException
     */
    private function getIdArrayForCase()
    {
        $case = $this->getCase($this->params()->fromRoute('case', null));

        $filter = [
            'case' => $case['id'],
        ];

        if (!is_null($case['licence'])) {
            $filter['licence'] = $case['licence']['id'];
        }

        if (!is_null($case['transportManager'])) {
            $filter['transportManager'] = $case['transportManager']['id'];
        }

        if (empty($filter)) {
            throw new \RuntimeException('Must be filtered by licence or transport manager');
        }

        return $filter;
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
        $form = $this->trait_getTaskForm($filters);

        $this->updateSelectValueOptions(
            $form->get('showTasks'),
            [
                FilterOptions::SHOW_SELF_ONLY => 'documents.filter.option.this-case-only',
            ]
        );

        return $form;
    }
}
