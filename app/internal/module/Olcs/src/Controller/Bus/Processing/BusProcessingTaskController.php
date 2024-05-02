<?php

namespace Olcs\Controller\Bus\Processing;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits;
use Olcs\Service\Data\SubCategory;

/**
 * Bus Processing Task controller
 * Bus task search and display
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BusProcessingTaskController extends AbstractController implements BusRegControllerInterface, LeftViewProvider
{
    use Traits\ProcessingControllerTrait, Traits\TaskActionTrait {
        Traits\TaskActionTrait::getTaskForm as traitGetTaskForm;
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
     * @return \Laminas\Form\FormInterface
     */
    protected function getTaskForm(array $filters = [])
    {
        $form = $this->traitGetTaskForm($filters);

        /** @var \Laminas\Form\Element\Select $option */
        $this->updateSelectValueOptions(
            $form->get('showTasks'),
            [
                FilterOptions::SHOW_SELF_ONLY => 'documents.filter.option.this-reg-only',
            ]
        );

        return $form;
    }
}
