<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Traits\ProcessingControllerTrait;

/**
 * Abstract Irhp Permit Processing Controller
 */
abstract class AbstractIrhpPermitProcessingController extends AbstractIrhpPermitController
{
    use ProcessingControllerTrait;

    protected TreeRouteStack $router;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        TreeRouteStack $router
    ) {
        parent::__construct($scriptFactory, $formHelper, $tableFactory, $viewHelperManager);
        $this->router = $router;
    }
}
