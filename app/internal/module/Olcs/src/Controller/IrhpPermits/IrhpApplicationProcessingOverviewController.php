<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\View\HelperPluginManager;
use Olcs\Helper\ApplicationProcessingHelper;

/**
 * Irhp Application Processing Overview Controller
 */
class IrhpApplicationProcessingOverviewController extends AbstractIrhpPermitProcessingController
{
    protected TreeRouteStack $router;
    protected $processingHelper;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        TreeRouteStack $router,
        ApplicationProcessingHelper $processingHelper
    ) {
        parent::__construct($scriptFactory, $formHelper, $tableFactory, $viewHelperManager, $router);
        $this->processingHelper = $processingHelper;
    }

    /**
     * index Action
     *
     * @return \Laminas\Http\Response
     */
    public function indexAction()
    {
        $options = [
            'query' => $this->getRequest()->getQuery()->toArray()
        ];

        return $this->redirectToRoute('licence/irhp-application-processing/notes', [], $options, true);
    }
}
