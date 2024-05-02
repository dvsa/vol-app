<?php

namespace Olcs\Controller\Operator;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Navigation\Navigation;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Traits;
use Olcs\Service\Data\Licence;
use Olcs\Service\Data\SubCategory;

/**
 * Operator Processing Tasks Controller
 */
class OperatorProcessingTasksController extends OperatorController
{
    use Traits\TaskActionTrait {
        Traits\TaskActionTrait::getTaskForm as traitGetTaskForm;
    }

    /**
     * @var string
     */
    protected $section = 'tasks';

    /**
     * @var string
     */
    protected $subNavRoute = 'operator_processing';

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        DateHelperService $dateHelper,
        AnnotationBuilder $transferAnnotationBuilder,
        CommandService $commandService,
        FlashMessengerHelperService $flashMessengerHelper,
        Licence $licenceDataService,
        QueryService $queryService,
        Navigation $navigation,
        protected SubCategory $subCategoryDataService
    ) {
        parent::__construct($scriptFactory, $formHelper, $tableFactory, $viewHelperManager, $dateHelper, $transferAnnotationBuilder, $commandService, $flashMessengerHelper, $licenceDataService, $queryService, $navigation);
    }

    /**
     * Get task action type
     *
     * @see    \Olcs\Controller\Traits\TaskActionTrait
     * @return string
     */
    protected function getTaskActionType()
    {
        return 'organisation';
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
     * @return \Laminas\Form\FormInterface
     */
    protected function getTaskForm(array $filters = [])
    {
        return $this->traitGetTaskForm($filters)
            ->remove('showTasks');
    }
}
