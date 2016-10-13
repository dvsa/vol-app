<?php

namespace Olcs\Controller\Application\Processing;

use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Olcs\Controller\Traits;

/**
 * Application Processing Tasks Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingTasksController extends AbstractApplicationProcessingController
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
        return 'application';
    }

    /**
     * Get task action filters
     *
     * @see \Olcs\Controller\Traits\TaskActionTrait
     * @return array
     */
    protected function getTaskActionFilters()
    {
        $appId = $this->params('application');

        return [
            'licence' => $this->getLicenceIdForApplication($appId),
            'assignedToTeam' => '',
            'assignedToUser' => '',
            'application' => $appId,
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
        $form = $this->trait_getTaskForm($filters);

        $this->updateSelectValueOptions(
            $form->get('showTasks'),
            [
                FilterOptions::SHOW_SELF_ONLY => 'documents.filter.option.this-app-only',
            ]
        );

        return $form;
    }
}
