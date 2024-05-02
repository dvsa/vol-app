<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Service\Data\DocumentSubCategory;

/**
 * IRHP Application Docs Controller
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpApplicationDocsController extends AbstractIrhpPermitController implements IrhpApplicationControllerInterface
{
    use ControllerTraits\DocumentActionTrait;
    use ControllerTraits\DocumentSearchTrait;

    protected DocumentSubCategory $docSubCategoryDataService;
    protected TranslationHelperService $translationHelper;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        protected FlashMessengerHelperService $flashMessengerHelper,
        DocumentSubCategory $docSubCategoryDataService,
        TranslationHelperService $translationHelper
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
        );
        $this->docSubCategoryDataService = $docSubCategoryDataService;
        $this->translationHelper = $translationHelper;
    }

    /**
     * Get configured document form
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return \Laminas\Form\FormInterface
     */
    protected function getConfiguredDocumentForm()
    {
        $filters = $this->getDocumentFilters();
        $form = $this->getDocumentForm($filters);

        $this->updateSelectValueOptions(
            $form->get('showDocs'),
            [
                FilterOptions::SHOW_SELF_ONLY => 'This application only',
            ]
        );

        return $form;
    }

    /**
     * Table to use
     *
     * @see    \Olcs\Controller\Traits\DocumentSearchTrait
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
        return 'licence/irhp-application-docs';
    }

    /**
     * Route params for document action redirects
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return [
            'irhpAppId' => $this->getFromRoute('irhpAppId'),
            'licence' => $this->getFromRoute('licence')
        ];
    }

    /**
     * Get document filters
     *
     * @return array
     */
    private function getDocumentFilters()
    {
        return $this->mapDocumentFilters(
            [
                'licence' => $this->getFromRoute('licence'),
                'irhpApplication' => $this->getFromRoute('irhpAppId'),
            ]
        );
    }

    /**
     * Get view model for document action
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return \Laminas\View\Model\ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->getDocumentFilters();

        return $this->getView(
            [
                'table' => $this->getDocumentsTable($filters)
            ]
        );
    }
}
