<?php

namespace Olcs\Controller\Bus\Docs;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Service\Data\DocumentSubCategory;

/**
 * Bus Docs Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BusDocsController extends AbstractController implements BusRegControllerInterface, LeftViewProvider
{
    use ControllerTraits\DocumentActionTrait;
    use ControllerTraits\DocumentSearchTrait;

    protected TranslationHelperService $translationHelper;
    protected DocumentSubCategory $docSubCategoryDataService;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        TranslationHelperService $translationHelper,
        DocumentSubCategory $docSubCategoryDataService
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager
        );
        $this->translationHelper = $translationHelper;
        $this->docSubCategoryDataService = $docSubCategoryDataService;

        $this->showDocsFilter = FilterOptions::SHOW_SELF_ONLY;
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
                FilterOptions::SHOW_SELF_ONLY => 'documents.filter.option.this-reg-only',
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
        return 'licence/bus-docs';
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
            'busRegId' => $this->getFromRoute('busRegId'),
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
                'busReg' => $this->getFromRoute('busRegId'),
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
