<?php

namespace Olcs\Controller\Operator\Docs;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Navigation\Navigation;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Operator\OperatorController;
use Olcs\Controller\Traits;
use Olcs\Service\Data\DocumentSubCategory;
use Olcs\Service\Data\Licence;

class OperatorDocsController extends OperatorController
{
    use Traits\DocumentSearchTrait;
    use Traits\DocumentActionTrait;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        DateHelperService $dateHelper,
        AnnotationBuilder $transferAnnotationBuilder,
        CommandService $commandService,
        FlashMessengerHelperService $flashMessengerHelper,
        Licence $licenceDataService,
        QueryService $queryService,
        Navigation $navigation,
        protected DocumentSubCategory $docSubCategoryDataService,
        protected TranslationHelperService $translationHelper
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $dateHelper,
            $transferAnnotationBuilder,
            $commandService,
            $flashMessengerHelper,
            $licenceDataService,
            $queryService,
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
        return 'operator/documents';
    }

    /**
     * Route params for document action redirects
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['organisation' => $this->getFromRoute('organisation')];
    }

    /**
     * Get view model for document action
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->mapDocumentFilters(['irfoOrganisation' => $this->getFromRoute('organisation')]);

        return $this->getViewWithOrganisation(
            [
                'table' => $this->getDocumentsTable($filters),
                'documents' => true,
            ]
        );
    }

    /**
     * Get Form
     *
     * @return \Laminas\Form\FieldsetInterface
     */
    protected function getConfiguredDocumentForm()
    {
        $filters = $this->mapDocumentFilters(['irfoOrganisation' => $this->getFromRoute('organisation')]);

        return $this->getDocumentForm($filters)
            ->remove('showDocs');
    }
}
