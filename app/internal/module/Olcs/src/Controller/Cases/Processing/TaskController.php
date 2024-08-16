<?php

namespace Olcs\Controller\Cases\Processing;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Service\Data\SubCategory;

class TaskController extends AbstractController implements CaseControllerInterface, LeftViewProvider
{
    use ControllerTraits\CaseControllerTrait, ControllerTraits\ProcessingControllerTrait, ControllerTraits\TaskActionTrait {
        ControllerTraits\TaskActionTrait::getTaskForm as traitGetTaskForm;
    }

    protected TreeRouteStack $router;

    protected string $helperClass;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        TreeRouteStack $router,
        protected SubCategory $subCategoryDataService
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager
        );
        $this->router = $router;
    }

    /**
     * Get task action type
     *
     * @see    \Olcs\Controller\Traits\TaskActionTrait
     * @return string
     */
    protected function getTaskActionType()
    {
        return 'case';
    }

    /**
     * Get task action filters
     *
     * @see    \Olcs\Controller\Traits\TaskActionTrait
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
     * @throw  \RuntimeException
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
     * @return \Laminas\Form\FormInterface
     */
    protected function getTaskForm(array $filters = [])
    {
        $form = $this->traitGetTaskForm($filters);

        $this->updateSelectValueOptions(
            $form->get('showTasks'),
            [
                FilterOptions::SHOW_SELF_ONLY => 'documents.filter.option.this-case-only',
            ]
        );

        return $form;
    }
}
