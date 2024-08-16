<?php

namespace Olcs\Controller\Application\Processing;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Traits;
use Olcs\Service\Data\SubCategory;

/**
 * Application Processing Tasks Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingTasksController extends AbstractApplicationProcessingController
{
    use Traits\TaskActionTrait {
        Traits\TaskActionTrait::getTaskForm as traitGetTaskForm;
    }

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        PluginManager $dataServiceManager,
        OppositionHelperService $oppositionHelper,
        ComplaintsHelperService $complaintsHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        TreeRouteStack $router,
        protected SubCategory $subCategoryDataService,
        $navigation
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $dataServiceManager,
            $oppositionHelper,
            $complaintsHelper,
            $flashMessengerHelper,
            $router,
            $navigation
        );
    }


    /**
     * @var string
     */
    protected $section = 'tasks';

    /**
     * Get task action type
     *
     * @see    \Olcs\Controller\Traits\TaskActionTrait
     * @return string
     */
    protected function getTaskActionType()
    {
        return 'application';
    }

    /**
     * Get task action filters
     *
     * @see    \Olcs\Controller\Traits\TaskActionTrait
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
     * @return \Laminas\Form\FormInterface
     */
    protected function getTaskForm(array $filters = [])
    {
        $form = $this->traitGetTaskForm($filters);

        $this->updateSelectValueOptions(
            $form->get('showTasks'),
            [
                FilterOptions::SHOW_SELF_ONLY => 'documents.filter.option.this-app-only',
            ]
        );

        return $form;
    }
}
