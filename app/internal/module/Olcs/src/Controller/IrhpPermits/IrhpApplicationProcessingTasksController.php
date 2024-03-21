<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Controller\Traits;
use Olcs\Service\Data\SubCategory;

/**
 * Irhp Application Processing Tasks Controller
 */
class IrhpApplicationProcessingTasksController extends AbstractIrhpPermitProcessingController implements
    IrhpApplicationControllerInterface
{
    use Traits\TaskActionTrait {
        Traits\TaskActionTrait::getTaskForm as traitGetTaskForm;
    }

    protected SubCategory $subCategoryDataService;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        TreeRouteStack $router,
        SubCategory $subCategoryDataService
    ) {
        $this->subCategoryDataService = $subCategoryDataService;
        parent::__construct($scriptFactory, $formHelper, $tableFactory, $viewHelperManager, $router);
    }

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
     * @return \Laminas\Form\FormInterface
     */
    protected function getTaskForm(array $filters = [])
    {
        return $this->traitGetTaskForm($filters)
            ->remove('showTasks');
    }
}
