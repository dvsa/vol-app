<?php

namespace Olcs\Controller\Application\Docs;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Application\ApplicationController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits;
use Olcs\Service\Data\DocumentSubCategory;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationDocsController extends ApplicationController implements LeftViewProvider
{
    use Traits\DocumentSearchTrait;
    use Traits\DocumentActionTrait;

    protected $navigation;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        PluginManager $dataServiceManager,
        OppositionHelperService $oppositionHelper,
        ComplaintsHelperService $complaintsHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        protected DocumentSubCategory $docSubCategoryDataService,
        protected TranslationHelperService $translationHelper,
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
    }

    /**
     * Table to use
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentTableName()
    {
        return 'documents';
    }

    /**
     * Route (prefix) for document action redirects
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'lva-application/documents';
    }

    /**
     * Route params for document action redirects
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['application' => $this->getFromRoute('application')];
    }

    /**
     * Get document filters
     *
     * @return array
     */
    private function getDocumentFilters()
    {
        $appId = $this->getFromRoute('application');
        $licence = $this->getLicenceIdForApplication($appId);

        return $this->mapDocumentFilters(
            [
                'licence' => $licence,
                'application' => $this->getFromRoute('application'),
                'showDocs' => FilterOptions::EXCLUDE_IRHP,
            ]
        );
    }

    /**
     * Get view model for document action
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->getDocumentFilters();

        $table = $this->getDocumentsTable($filters);

        return $this->getViewWithApplication(['table' => $table]);
    }

    /**
     * Get Customized Document Form
     *
     * @return \Laminas\Form\FormInterface
     */
    protected function getConfiguredDocumentForm()
    {
        $filters = $this->getDocumentFilters();

        $form = $this->getDocumentForm($filters);

        $this->updateSelectValueOptions(
            $form->get('showDocs'),
            [
                FilterOptions::SHOW_SELF_ONLY => 'documents.filter.option.this-app-only',
                FilterOptions::EXCLUDE_IRHP => 'documents.filter.option.exclude-irhp',
            ]
        );

        return $form;
    }
}
