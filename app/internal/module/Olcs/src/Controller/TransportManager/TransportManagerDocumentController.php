<?php

namespace Olcs\Controller\TransportManager;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits;
use Olcs\Service\Data\DocumentSubCategory;

class TransportManagerDocumentController extends TransportManagerController implements LeftViewProvider
{
    use Traits\DocumentActionTrait;
    use Traits\DocumentSearchTrait;
    use Traits\ListDataTrait;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        FlashMessengerHelperService $flashMessengerHelper,
        TranslationHelperService $translationHelper,
        $navigation,
        protected DocumentSubCategory $docSubCategoryDataService
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $translationHelper,
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
     * @var string
     */
    protected $section = 'documents';

    /**
     * Process action - Index
     *
     * @return \Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        // the action needs to be index. Otherwise the action name will get appended to urls in the TM menu
        return $this->documentsAction();
    }

    /**
     * Route (prefix) for document action redirects
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'transport-manager/documents';
    }

    /**
     * Route params for document action redirects
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['transportManager' => $this->getFromRoute('transportManager')];
    }

    /**
     * Get view model for document action
     *
     * @see    \Olcs\Controller\Traits\DocumentActionTrait
     * @return \Laminas\View\Model\ViewModel
     */
    protected function getDocumentView()
    {
        $transportManager = $this->getFromRoute('transportManager');

        $filters = $this->mapDocumentFilters(['transportManager' => $transportManager]);

        $table = $this->getDocumentsTable($filters);

        return $this->getViewWithTm(['table' => $table]);
    }

    /**
     * Get Form
     *
     * @return \Laminas\Form\FieldsetInterface
     */
    protected function getConfiguredDocumentForm()
    {
        $transportManager = $this->getFromRoute('transportManager');

        $filters = $this->mapDocumentFilters(['transportManager' => $transportManager]);

        return $this->getDocumentForm($filters)
            ->remove('showDocs');
    }
}
