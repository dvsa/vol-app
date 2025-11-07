<?php

namespace Olcs\Controller\Document;

use Common\Category;
use Common\Form\Elements\InputFilters\MultiCheckboxEmpty;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Document\CreateLetter;
use Dvsa\Olcs\Transfer\Query\Document\TemplateParagraphs;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Logging\Log\Logger;
use Olcs\Service\Data\DocumentSubCategoryWithDocs;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Dvsa\Olcs\Transfer\Query\DocTemplate\ById as DocTemplateById;
use Common\FeatureToggle;

class DocumentGenerationController extends AbstractDocumentController
{
    /**
     * Labels for empty select options
     */
    public const EMPTY_LABEL = 'Please select';

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        array $config,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected DocumentSubCategoryWithDocs $docSubcategoryWithDocsDataService
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $config
        );
    }

    /**
     * Process action - Generate
     *
     * @return ViewModel
     */
    public function generateAction()
    {
        $form = $this->generateForm('GenerateDocument', [$this, 'processGenerate']);

        $this->loadScripts(['generate-document']);

        $view = new ViewModel(['form' => $form]);

        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Generate letter');
    }

    /**
     * Process action - List Template Bookmarks
     *
     * @return ViewModel
     */
    public function listTemplateBookmarksAction()
    {
        $templateId = (int) $this->params('id');

        // Check if this template uses database-driven letter type
        if ($this->isLettersDatabaseDrivenEnabled() && $this->templateHasLetterType($templateId)) {
            // Return JSON response indicating redirect is needed
            return new \Laminas\View\Model\JsonModel([
                'redirectToNewLetterFlow' => true,
                'templateId' => $templateId
            ]);
        }

        $form = new Form();

        $fieldset = new Fieldset();
        $fieldset->setLabel('documents.bookmarks');
        $fieldset->setName('bookmarks');

        $form->add($fieldset);

        $this->addTemplateBookmarks($templateId, $fieldset);

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $view;
    }

    /**
     * Wrap the callback with a try/catch to handle any bookmark errors.
     *
     * @param array $data Form Data
     *
     * @return null|\Laminas\Http\Response
     */
    public function processGenerate($data)
    {
        try {
            return $this->processGenerateDocument($data);
        } catch (\ErrorException $e) {
            Logger::warn($e->getMessage());
            $this->flashMessengerHelper
                ->addCurrentErrorMessage('Unable to generate the document');
        }

        return null;
    }

    /**
     * Generate document
     *
     * @param array $data Form Data
     *
     * @return \Laminas\Http\Response
     * @throws \ErrorException
     */
    protected function processGenerateDocument($data)
    {
        $data = $data['validData'];
        $routeParams = $this->params()->fromRoute();

        // Check if we should use new database-driven letter pathway
        if ($this->shouldRedirectToLetterChoices($data)) {
            return $this->redirectToLetterChoices($data, $routeParams);
        }

        $queryData = array_merge($data, $routeParams);

        // if both the entityType and the entityId has some values then add it into $queryData
        if (!empty($routeParams['entityType']) && !empty($routeParams['entityId'])) {
            $queryData[$routeParams['entityType']] = $routeParams['entityId'];
        }

        // we need to link certain documents to multiple IDs
        switch ($routeParams['type']) {
            case 'application':
                $queryData['licence'] = $this->getLicenceIdForApplication();
                break;
            case 'case':
                $queryData = array_merge($queryData, $this->getCaseData());
                break;
            case 'busReg':
                $queryData['licence'] = $routeParams['licence'];
                break;
            // fixing irfoOrganisation / organisation ambiguity
            case 'irhpApplication':
                $queryData['licence'] = $routeParams['licence'];
                break;
            // fixing irfoOrganisation / organisation ambiguity
            case 'irfoOrganisation':
                $queryData['irfoOrganisation'] = $routeParams['organisation'];
                unset($queryData['organisation']);
                break;
            default:
                break;
        }

        //  get licence data
        if (!empty($queryData['licence'])) {
            $licence = $this->getLicence($queryData['licence']);

            $queryData += [
                'goodsOrPsv' => $licence['goodsOrPsv']['id'] ?? null,
                'licenceType' => $licence['licenceType']['id'] ?? null,
                'organisation' => $licence['organisation']['id'] ?? null,
            ];
        }

        $dto = CreateLetter::create(
            [
                'template' => $data['details']['documentTemplate'],
                'data' => $queryData,
                'meta' => json_encode(['details' => $data['details'], 'bookmarks' => $data['bookmarks']]),
                'disableBookmarks' => $this->isProposeToRevoke($data)
            ]
        );
        $response = $this->handleCommand($dto);

        if (!$response->isOk()) {
            throw new \ErrorException('Error creating letter: ' . $response->getBody());
        }

        // we don't know what params are needed to satisfy this type's
        // finalise route; so to be safe we supply them all
        $redirectParams = array_merge(
            $routeParams,
            [
                'doc' => $response->getResult()['id']['document']
            ]
        );

        $redirectParams['action'] = null;

        $additionalQueryParams = [
            'taskId' => $response->getResult()['id']['task'] ?? null
        ];

        return $this->redirectToDocumentRoute(
            $routeParams['type'],
            'finalise',
            $redirectParams,
            false,
            $additionalQueryParams
        );
    }

    /**
     * Make changes in form in depend form selected options
     *
     * @param \Laminas\Form\FormInterface $form Form
     *
     * @return mixed
     */
    protected function alterFormBeforeValidation($form)
    {
        $entityType = $this->getFromRoute('entityType');

        $categoryMapType = !empty($entityType) ? $this->getFromRoute('entityType') : $this->params('type');

        $defaultData = [
            'details' => ['category' => $this->getCategoryForType($categoryMapType)]
        ];

        $data = [];

        /**
 * @var \Laminas\Http\Request $request
*/
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } elseif ($this->params('doc')) {
            $data = $this->fetchDocData();
            $this->removeDocument($this->params('doc'));
        }

        $data = array_merge($defaultData, $data);

        $details = $data['details'] ?? [];

        $catId = (int)$details['category'];

        //  set dynamic select
        $this->docSubcategoryWithDocsDataService
            ->setCategory($catId);

        $docTemplates = ['' => self::EMPTY_LABEL];
        if (isset($details['documentSubCategory'])) {
            $subCategoryId = (int) $details['documentSubCategory'];
            $docTemplates = $this->getListDataDocTemplates(null, $subCategoryId);
        }

        $form->get('details')->get('documentTemplate')->setValueOptions($docTemplates);

        if (isset($details['documentTemplate'])) {
            $this->addTemplateBookmarks($details['documentTemplate'], $form->get('bookmarks'));
        }

        $form->setData($data);

        return $form;
    }

    /**
     * Add template bookmarks
     *
     * @param int                             $id       Template Id
     * @param \Laminas\Form\FieldsetInterface $fieldset Target container element
     *
     * @return void
     */
    private function addTemplateBookmarks($id, $fieldset)
    {
        if (empty($id)) {
            return;
        }

        $response = $this->handleQuery(TemplateParagraphs::create(['id' => $id]));
        if (!$response->isOk()) {
            return;
        }

        $result = $response->getResult();

        $bookmarks = $result['docTemplateBookmarks'];

        foreach ($bookmarks as $bookmark) {
            $bookmark = $bookmark['docBookmark'];

            $description = (empty($bookmark['description']) ? $bookmark['name'] : $bookmark['description']);

            $paragraphs = (array) ($bookmark['docParagraphBookmarks'] ?: null);
            if (0 === count($paragraphs)) {
                continue;
            }

            $element = new MultiCheckboxEmpty();
            $element->setLabel($description);
            $element->setName($bookmark['name']);
            // user-supplied bookmarks are *all* optional
            $element->setOptions(['required' => false]);

            $options = [];
            foreach ($paragraphs as $paragraph) {
                $paragraph = $paragraph['docParagraph'];
                $options[$paragraph['id']] = $paragraph['paraTitle'];
            }
            $element->setValueOptions($options);

            $fieldset->add($element);
        }
    }

    protected function isProposeToRevoke($data): bool
    {
        if (
            $data['details']['category'] === (string) Category::CATEGORY_COMPLIANCE
            && $data['details']['documentSubCategory'] === (string) Category::DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if template requires new letter choices flow
     *
     * @param array $data Form data
     * @return bool
     */
    protected function shouldRedirectToLetterChoices(array $data): bool
    {
        // Check feature toggle first (fast exit)
        if (!$this->isLettersDatabaseDrivenEnabled()) {
            return false;
        }

        // Check if template has letterType
        $templateId = $data['details']['documentTemplate'] ?? null;
        if (!$templateId) {
            return false;
        }

        return $this->templateHasLetterType($templateId);
    }

    /**
     * Check if letters database-driven feature is enabled
     *
     * @return bool
     */
    protected function isLettersDatabaseDrivenEnabled(): bool
    {
        $result = $this->handleQuery(
            IsEnabledQry::create(['ids' => [FeatureToggle::LETTERS_DATABASE_DRIVEN]])
        );

        return $result->getResult()['isEnabled'] ?? false;
    }

    /**
     * Check if template uses database-driven letter type
     *
     * @param int $templateId Template ID
     * @return bool
     */
    protected function templateHasLetterType(int $templateId): bool
    {
        $response = $this->handleQuery(DocTemplateById::create(['id' => $templateId]));

        if (!$response->isOk()) {
            return false;
        }

        $templateData = $response->getResult();
        return !empty($templateData['letterType']['id']);
    }

    /**
     * Redirect to letter generation (database-driven letters)
     *
     * @param array $data Form data
     * @param array $routeParams Route parameters
     * @return \Laminas\Http\Response
     */
    protected function redirectToLetterChoices(array $data, array $routeParams)
    {
        $templateId = $data['details']['documentTemplate'];

        // Build query parameters for the new unified letter generation route
        $queryParams = [
            'template' => $templateId
        ];

        // Add entity context based on route type
        $entityType = $routeParams['type'] ?? null;
        $entityId = null;

        // Map route type to entity parameter
        switch ($entityType) {
            case 'licence':
                $entityId = $routeParams['licence'] ?? null;
                if ($entityId) {
                    $queryParams['licence'] = $entityId;
                }
                break;
            case 'application':
                $entityId = $routeParams['application'] ?? null;
                if ($entityId) {
                    $queryParams['application'] = $entityId;
                }
                break;
            case 'busReg':
                $entityId = $routeParams['busRegId'] ?? null;
                if ($entityId) {
                    $queryParams['busReg'] = $entityId;
                }
                break;
            case 'transportManager':
                $entityId = $routeParams['transportManager'] ?? null;
                if ($entityId) {
                    $queryParams['transportManager'] = $entityId;
                }
                break;
            case 'irhpApplication':
                $entityId = $routeParams['irhpAppId'] ?? null;
                if ($entityId) {
                    $queryParams['irhpApplication'] = $entityId;
                }
                break;
            case 'irfoOrganisation':
                $entityId = $routeParams['organisation'] ?? null;
                if ($entityId) {
                    $queryParams['irfoOrganisation'] = $entityId;
                }
                break;
        }

        // Store current page as return URL for navigation back
        $currentUrl = $this->getRequest()->getRequestUri();
        $queryParams['returnUrl'] = $currentUrl;

        // Redirect to new unified letter generation route
        return $this->redirect()->toRoute(
            'letter/create',
            [],
            ['query' => $queryParams]
        );
    }
}
