<?php

namespace Olcs\Controller\Cases\Docs;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Service\Data\DocumentSubCategory;

/**
 * Case Docs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CaseDocsController extends AbstractController implements CaseControllerInterface, LeftViewProvider
{
    use ControllerTraits\CaseControllerTrait;
    use ControllerTraits\DocumentActionTrait;
    use ControllerTraits\DocumentSearchTrait;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        protected TranslationHelperService $translationHelper,
        protected DocumentSubCategory $docSubCategoryDataService
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager
        );
    }

    /**
     * Table to use
     *
     * @see    \Olcs\Controller\Traits\DocumentSearchTrait
     * @return string
     */
    protected function getDocumentTableName()
    {
        return 'documents-with-sla';
    }

    /**
     * Route (prefix) for document action redirects
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'case_licence_docs_attachments';
    }

    /**
     * Route params for document action redirects
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['case' => $this->getFromRoute('case')];
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

        $table = $this->getDocumentsTable($filters);

        return $this->getView(['table' => $table]);
    }

    /**
     * Get document filters
     *
     * @return array
     */
    private function getDocumentFilters()
    {
        $case = $this->getCase();

        $filters = [
            'case' => $case['id'],
            'showDocs' => FilterOptions::EXCLUDE_IRHP,
        ];

        switch ($case['caseType']['id']) {
            case 'case_t_tm':
                $filters['transportManager'] = $case['transportManager']['id'];
                break;
            default:
                $filters['licence'] = $case['licence']['id'];
                break;
        }

        return $this->mapDocumentFilters($filters);
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
                FilterOptions::SHOW_SELF_ONLY => 'documents.filter.option.this-case-only',
                FilterOptions::EXCLUDE_IRHP => 'documents.filter.option.exclude-irhp',
            ]
        );

        return $form;
    }
}
