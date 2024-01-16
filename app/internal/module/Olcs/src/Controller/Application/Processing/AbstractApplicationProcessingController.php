<?php

/**
 * Abstract Application Processing Controller
 */

namespace Olcs\Controller\Application\Processing;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Application\ApplicationController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\ProcessingControllerTrait;

/**
 * Abstract Application Processing Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractApplicationProcessingController extends ApplicationController implements LeftViewProvider
{
    use ProcessingControllerTrait;

    protected $helperClass = '\Olcs\Helper\ApplicationProcessingHelper';

    protected TreeRouteStack $router;

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
            $navigation
        );
        $this->router = $router;
    }

    /**
     * get method for Navigation config
     *
     * @return array
     */
    protected function getNavigationConfig()
    {
        $application = $this->getApplication();

        return $this->getProcessingHelper()->getNavigation(
            $application['id'],
            $this->section
        );
    }
}
