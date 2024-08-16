<?php

/**
 * Abstract Licence Processing Controller
 */

namespace Olcs\Controller\Licence\Processing;

use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Licence\LicenceController;
use Olcs\Controller\Traits\ProcessingControllerTrait;
use Olcs\Service\Data\SubCategory;

/**
 * Abstract Licence Processing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractLicenceProcessingController extends LicenceController implements LeftViewProvider
{
    use ProcessingControllerTrait;

    protected $helperClass = \Olcs\Helper\LicenceProcessingHelper::class;
    protected TreeRouteStack $router;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        OppositionHelperService $oppositionHelper,
        ComplaintsHelperService $complaintsHelper,
        $navigation,
        protected SubCategory $subCategoryDataService,
        FlashMessengerHelperService $flashMessengerHelper,
        TreeRouteStack $router
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $oppositionHelper,
            $complaintsHelper,
            $navigation,
            $flashMessengerHelper
        );
        $this->router = $router;
    }

    /**
     * get method Navigation Config
     *
     * @return array
     */
    protected function getNavigationConfig()
    {
        $licence = $this->getLicence();

        return $this->getProcessingHelper()->getNavigation(
            $licence['id'],
            $this->section
        );
    }

    /**
     * get method Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }
}
