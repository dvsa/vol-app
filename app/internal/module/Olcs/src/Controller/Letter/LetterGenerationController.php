<?php

namespace Olcs\Controller\Letter;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Http\Response;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * Letter Generation Controller
 * Handles database-driven letter creation workflow
 */
class LetterGenerationController extends AbstractInternalController implements ToggleAwareInterface, LeftViewProvider
{
    /**
     * Left sidebar view for preview page
     */
    protected ?ViewModel $leftView = null;

    /**
     * Feature toggle configuration
     */
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::LETTERS_DATABASE_DRIVEN,
        ],
    ];

    /**
     * Inline scripts for form handling
     */
    protected $inlineScripts = [
        'createAction' => ['forms/letter-generation'],
        'previewAction' => ['forms/letter-preview'],
        'editAction' => ['forms/letter-edit'],
    ];

    /**
     * Navigation ID for breadcrumbs (set dynamically per action)
     */
    protected $navigationId = '';

    /**
     * Constructor
     */
    public function __construct(
        TranslationHelperService $translationHelperService,
        FormHelperService $formHelperService,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation
    ) {
        parent::__construct(
            $translationHelperService,
            $formHelperService,
            $flashMessengerHelperService,
            $navigation
        );
    }

    /**
     * Get left sidebar view (implements LeftViewProvider)
     *
     * @return ViewModel|null
     */
    public function getLeftView(): ?ViewModel
    {
        return $this->leftView;
    }

    /**
     * Create letter action - main entry point
     *
     * Query parameters expected:
     * - licence: Licence ID (optional)
     * - application: Application ID (optional)
     * - busReg: Bus Registration ID (optional)
     * - transportManager: Transport Manager ID (optional)
     * - irhpApplication: IRHP Application ID (optional)
     * - irfoOrganisation: IRFO Organisation ID (optional)
     * - template: Template ID (required)
     * - returnUrl: URL to return to after completion (optional)
     *
     * @return ViewModel|Response
     */
    public function createAction()
    {
        // Get query parameters
        $queryParams = $this->getRequest()->getQuery()->toArray();

        // Get route parameters and merge with query params (route params take precedence)
        $routeParams = $this->extractRouteParams();
        $allParams = array_merge($queryParams, $routeParams);

        // Add entity context to query params so it's included in generate URL
        $queryParams = $allParams;

        // Validate required parameters
        if (!isset($queryParams['template'])) {
            $this->flashMessengerHelperService->addErrorMessage('Template ID is required');
            return $this->redirectToReturnUrl($queryParams);
        }

        $templateId = (int) $queryParams['template'];

        // Extract entity context from query and route params
        $entityContext = $this->extractEntityContext($allParams);

        // Build accordion data structure with issue types and their issues
        $accordionData = $this->buildAccordionData();

        $view = new ViewModel([
            'templateId' => $templateId,
            'entityContext' => $entityContext,
            'accordionData' => $accordionData,
            'queryParams' => $queryParams,
        ]);

        $view->setTemplate('pages/letter/create');

        // Set page title for the layout
        $this->placeholder()->setPlaceholder('contentTitle', 'Create Letter');

        return $this->viewBuilder()->buildView($view);
    }

    /**
     * Generate action - handles form submission to create letter instance
     *
     * @return Response
     */
    public function generateAction()
    {
        $postData = $this->getRequest()->getPost()->toArray();
        if (empty($postData)) {
            return $this->jsonError('Method not allowed', 405);
        }

        $queryParams = $this->getRequest()->getQuery()->toArray();

        // Get route parameters and merge with query params (route params take precedence)
        $routeParams = $this->extractRouteParams();
        $allParams = array_merge($queryParams, $routeParams);

        if (empty($postData['letterType'])) {
            return $this->jsonError('Template is required');
        }

        $templateId = (int) $postData['letterType'];
        $template = $this->fetchTemplateById($templateId);

        if (!$template || empty($template['letterType']['id'])) {
            return $this->jsonError('Invalid template or template has no letter type');
        }

        $letterTypeId = $template['letterType']['id'];

        $entityContext = $this->extractEntityContext($allParams);


        $commandData = [
            'letterType' => $letterTypeId,
            'selectedIssues' => $postData['letterIssues'] ?? [],
        ];

        if (!empty($entityContext['type'])) {
            $commandData[$entityContext['type']] = $entityContext['id'];
        }

        $command = \Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\Generate::create($commandData);

        $response = $this->handleCommand($command);


        if (!$response->isOk()) {
            $messages = $response->getResult()['messages'] ?? [];
            $errorMessage = is_array($messages) ? json_encode($messages) : $messages;
            return $this->jsonError('Failed to generate letter: ' . $errorMessage);
        }

        $result = $response->getResult();
        $letterInstanceId = $result['id']['letterInstance'] ?? null;

        if (!$letterInstanceId) {
            return $this->jsonError('Letter generated but ID not returned');
        }

        $letterInstanceData = $this->fetchLetterInstanceById($letterInstanceId);

        return $this->jsonSuccess([
            'letterInstanceId' => $letterInstanceId,
            'message' => $result['messages'][0] ?? 'Letter generated successfully',
            'redirectUrl' => '#',
            'letterInstance' => $letterInstanceData,
        ]);
    }

    /**
     * Preview letter action - displays read-only preview in a new tab
     *
     * Query parameters expected:
     * - id: Letter Instance ID (required)
     *
     * @return ViewModel|Response
     */
    public function previewAction()
    {
        // Clear any stale flash messages from previous session
        $this->flashMessenger()->clearCurrentMessagesFromContainer();

        $letterInstanceId = $this->params()->fromQuery('id');

        if (!$letterInstanceId) {
            $this->flashMessengerHelperService->addErrorMessage('Letter instance ID is required');
            return $this->redirect()->toRoute('dashboard');
        }

        // Fetch letter instance data (not the full preview HTML - that's loaded in iframe)
        $query = \Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\Get::create([
            'id' => (int) $letterInstanceId
        ]);

        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            $this->flashMessengerHelperService->addErrorMessage('Letter not found');
            return $this->redirect()->toRoute('dashboard');
        }

        $result = $response->getResult();

        // Build sections list for sidebar
        $sectionsList = [];
        foreach ($result['letterInstanceIssues'] ?? [] as $issue) {
            $sectionsList[] = [
                'id' => $issue['id'],
                'name' => $issue['letterIssueVersion']['heading'] ?? 'Issue',
                'type' => 'issue',
            ];
        }

        // Set left sidebar BEFORE calling viewBuilder (LeftViewProvider interface)
        $sidebarView = new ViewModel([
            'letterInstance' => $result,
            'sectionsList' => $sectionsList,
        ]);
        $sidebarView->setTemplate('pages/letter/preview-sidebar');
        $this->leftView = $sidebarView;

        $view = new ViewModel([
            'letterInstanceId' => $letterInstanceId,
            'letterInstance' => $result,
            'sectionsList' => $sectionsList,
        ]);

        $view->setTemplate('pages/letter/preview');

        // Set navigation for breadcrumbs
        $this->navigationId = 'letter_preview';
        $this->setNavigationCurrentLocation();

        // Set page title in header strip
        $this->placeholder()->setPlaceholder('pageTitle', 'Preview and edit letter');

        return $this->viewBuilder()->buildView($view);
    }

    /**
     * Preview content action - returns raw letter HTML for iframe
     *
     * Query parameters expected:
     * - id: Letter Instance ID (required)
     *
     * @return ViewModel|Response
     */
    public function previewContentAction()
    {
        $letterInstanceId = $this->params()->fromQuery('id');

        if (!$letterInstanceId) {
            $response = $this->getResponse();
            $response->setStatusCode(400);
            $response->setContent('<html><body><p>Letter instance ID is required</p></body></html>');
            return $response;
        }

        // Fetch letter instance with preview HTML
        $query = \Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\Preview::create([
            'id' => (int) $letterInstanceId
        ]);

        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            $httpResponse = $this->getResponse();
            $httpResponse->setStatusCode(404);
            $httpResponse->setContent('<html><body><p>Letter not found</p></body></html>');
            return $httpResponse;
        }

        $result = $response->getResult();

        // Return raw HTML for iframe - this is a complete HTML document
        $httpResponse = $this->getResponse();
        $httpResponse->setContent($result['previewHtml'] ?? '');
        $httpResponse->getHeaders()->addHeaderLine('Content-Type', 'text/html; charset=utf-8');
        return $httpResponse;
    }

    /**
     * Edit letter sections action
     *
     * Query parameters expected:
     * - id: Letter Instance ID (required)
     * - sections[]: Selected issue IDs to edit (required)
     *
     * @return ViewModel|Response
     */
    public function editAction()
    {
        $letterInstanceId = $this->params()->fromQuery('id');
        $selectedSections = $this->params()->fromQuery('sections', []);

        if (!$letterInstanceId) {
            $this->flashMessengerHelperService->addErrorMessage('Letter instance ID is required');
            return $this->redirect()->toRoute('dashboard');
        }

        if (empty($selectedSections)) {
            $this->flashMessengerHelperService->addErrorMessage('No sections selected');
            return $this->redirect()->toUrl('/letter/preview?id=' . urlencode($letterInstanceId));
        }

        $letterInstance = $this->fetchLetterInstanceById((int) $letterInstanceId);

        if (!$letterInstance) {
            $this->flashMessengerHelperService->addErrorMessage('Letter not found');
            return $this->redirect()->toRoute('dashboard');
        }

        $groupedIssues = [];
        foreach ($letterInstance['letterInstanceIssues'] ?? [] as $issue) {
            if (!in_array($issue['id'], $selectedSections)) {
                continue;
            }

            $issueVersion = $issue['letterIssueVersion'] ?? [];
            $issueType = $issueVersion['letterIssueType'] ?? null;
            $typeName = $issueType['name'] ?? 'Other';
            $typeId = $issueType['id'] ?? 0;

            if (!isset($groupedIssues[$typeId])) {
                $groupedIssues[$typeId] = [
                    'typeName' => $typeName,
                    'issues' => [],
                ];
            }

            $editedContent = $issue['editedContent'] ?? null;
            $defaultContent = $issueVersion['defaultBodyContent'] ?? null;

            if (!empty($editedContent)) {
                $effectiveContent = is_string($editedContent)
                    ? $editedContent
                    : json_encode($editedContent);
            } elseif (!empty($defaultContent)) {
                $effectiveContent = is_string($defaultContent)
                    ? $defaultContent
                    : json_encode($defaultContent);
            } else {
                $effectiveContent = json_encode(['blocks' => [], 'version' => '2.28.2']);
            }

            $groupedIssues[$typeId]['issues'][] = [
                'id' => $issue['id'],
                'heading' => $issueVersion['heading'] ?? 'Issue',
                'content' => $effectiveContent,
                'version' => $issue['version'] ?? 1,
            ];
        }

        $view = new ViewModel([
            'letterInstanceId' => $letterInstanceId,
            'letterInstance' => $letterInstance,
            'groupedIssues' => $groupedIssues,
        ]);

        $view->setTemplate('pages/letter/edit');

        // Set navigation for breadcrumbs
        $this->navigationId = 'letter_edit';
        $this->setNavigationCurrentLocation();

        $this->placeholder()->setPlaceholder('pageTitle', 'Edit letter sections');

        return $this->viewBuilder()->buildView($view);
    }

    /**
     * Save issue content action - AJAX endpoint
     *
     * Accepts POST with JSON body: { issueId, editedContent, version }
     *
     * @return Response
     */
    public function saveIssueContentAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->jsonError('Method not allowed', 405);
        }

        $body = json_decode($this->getRequest()->getContent(), true);

        if (empty($body['issueId']) || !isset($body['editedContent']) || empty($body['version'])) {
            return $this->jsonError('Missing required fields: issueId, editedContent, version');
        }

        $command = \Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceIssue\UpdateContent::create([
            'id' => (int) $body['issueId'],
            'editedContent' => is_string($body['editedContent'])
                ? $body['editedContent']
                : json_encode($body['editedContent']),
            'version' => (int) $body['version'],
        ]);

        $response = $this->handleCommand($command);

        if (!$response->isOk()) {
            $messages = $response->getResult()['messages'] ?? [];
            $errorMessage = is_array($messages) ? implode(', ', $messages) : $messages;
            return $this->jsonError('Failed to save: ' . $errorMessage);
        }

        $result = $response->getResult();

        return $this->jsonSuccess([
            'issueId' => (int) $body['issueId'],
            'message' => $result['messages'][0] ?? 'Saved successfully',
            'version' => ($body['version'] + 1),
        ]);
    }

    /**
     * Return JSON error response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @return Response
     */
    protected function jsonError(string $message, int $statusCode = 400): Response
    {
        $response = $this->getResponse();
        $response->setStatusCode($statusCode);
        $response->setContent(json_encode([
            'success' => false,
            'message' => $message,
        ]));
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Return JSON success response
     *
     * @param array $data Response data
     * @return Response
     */
    protected function jsonSuccess(array $data): Response
    {
        $response = $this->getResponse();
        $response->setContent(json_encode(array_merge(['success' => true], $data)));
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Build accordion data structure with issue types and their issues
     *
     * @return array Array of ['issueType' => [...], 'issues' => [...]]
     */
    protected function buildAccordionData(): array
    {
        // Fetch all active issue types ordered by display order
        $issueTypes = $this->fetchActiveIssueTypes();

        // Fetch all active letter issues
        $letterIssues = $this->fetchActiveLetterIssues();

        // Group issues by issue type ID
        $issuesByType = [];
        foreach ($letterIssues as $issue) {
            $typeId = $issue['currentVersion']['letterIssueType']['id'] ?? null;
            if ($typeId) {
                if (!isset($issuesByType[$typeId])) {
                    $issuesByType[$typeId] = [];
                }
                $issuesByType[$typeId][] = $issue;
            }
        }

        // Build final structure
        $accordionData = [];
        foreach ($issueTypes as $issueType) {
            $typeId = $issueType['id'];
            $accordionData[] = [
                'issueType' => $issueType,
                'issues' => $issuesByType[$typeId] ?? [],
            ];
        }

        return $accordionData;
    }

    /**
     * Fetch active issue types ordered by display order
     *
     * @return array
     */
    protected function fetchActiveIssueTypes(): array
    {
        $query = \Dvsa\Olcs\Transfer\Query\Letter\LetterIssueType\GetList::create([
            'sort' => 'displayOrder',
            'order' => 'ASC',
            'page' => 1,
            'limit' => 100,
        ]);

        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            return [];
        }

        $result = $response->getResult();

        // Filter active issue types only
        $issueTypes = array_filter($result['results'] ?? [], function ($issueType) {
            return !empty($issueType['isActive']);
        });

        return array_values($issueTypes);
    }

    /**
     * Fetch all active letter issues with their current version data
     *
     * @return array
     */
    protected function fetchActiveLetterIssues(): array
    {
        $query = \Dvsa\Olcs\Transfer\Query\Letter\LetterIssue\GetList::create([
            'sort' => 'issueKey',
            'order' => 'ASC',
            'page' => 1,
            'limit' => 100, // Maximum allowed limit
        ]);

        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            return [];
        }

        $result = $response->getResult();

        return $result['results'] ?? [];
    }

    /**
     * Fetch document template by ID
     *
     * @param int $templateId Template ID
     * @return array|null Template data or null if not found
     */
    protected function fetchTemplateById(int $templateId): ?array
    {
        $query = \Dvsa\Olcs\Transfer\Query\DocTemplate\ById::create([
            'id' => $templateId,
        ]);

        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            return null;
        }

        return $response->getResult();
    }

    /**
     * Fetch letter instance by ID with all related data
     *
     * @param int $letterInstanceId Letter instance ID
     * @return array|null Letter instance data or null if not found
     */
    protected function fetchLetterInstanceById(int $letterInstanceId): ?array
    {
        $query = \Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\Get::create([
            'id' => $letterInstanceId,
        ]);

        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            return null;
        }

        return $response->getResult();
    }

    /**
     * Extract entity context from query parameters
     *
     * @param array $queryParams Query parameters
     * @return array Entity context with type and ID
     */
    protected function extractEntityContext(array $queryParams): array
    {
        $entityTypes = [
            'licence',
            'application',
            'case',
            'busReg',
            'transportManager',
            'irhpApplication',
            'irfoOrganisation'
        ];

        foreach ($entityTypes as $type) {
            if (isset($queryParams[$type])) {
                return [
                    'type' => $type,
                    'id' => (int) $queryParams[$type]
                ];
            }
        }

        return [];
    }

    /**
     * Extract entity context from route parameters
     *
     * Maps actual route parameter names to command parameter names
     *
     * @return array Route parameters with entity IDs
     */
    protected function extractRouteParams(): array
    {
        // Map route parameter names to command parameter names
        $routeParamMap = [
            'licence' => 'licence',
            'application' => 'application',
            'case' => 'case',
            'busRegId' => 'busReg',
            'transportManager' => 'transportManager',
            'irhpAppId' => 'irhpApplication',
            'organisation' => 'irfoOrganisation', // May need adjustment
        ];

        $routeParams = [];
        foreach ($routeParamMap as $routeParam => $commandParam) {
            $value = $this->params()->fromRoute($routeParam);
            if ($value !== null) {
                $routeParams[$commandParam] = $value;
            }
        }

        return $routeParams;
    }

    /**
     * Redirect back to source or default location
     *
     * @param array $queryParams Query parameters
     * @return Response
     */
    protected function redirectToReturnUrl(array $queryParams): Response
    {
        if (isset($queryParams['returnUrl'])) {
            return $this->redirect()->toUrl($queryParams['returnUrl']);
        }

        // Default fallback - redirect to appropriate documents page based on context
        $entityContext = $this->extractEntityContext($queryParams);

        if (!empty($entityContext['type'])) {
            return $this->redirectToDocumentsPage($entityContext);
        }

        // Ultimate fallback - dashboard
        return $this->redirect()->toRoute('dashboard');
    }

    /**
     * Redirect to appropriate documents page based on entity type
     *
     * @param array $entityContext Entity context
     * @return Response
     */
    protected function redirectToDocumentsPage(array $entityContext): Response
    {
        $documentRouteMap = [
            'licence' => ['route' => 'licence/documents', 'param' => 'licence'],
            'application' => ['route' => 'lva-application/documents', 'param' => 'application'],
            'busReg' => ['route' => 'licence/bus-docs', 'param' => 'busRegId'],
            'transportManager' => ['route' => 'transport-manager/documents', 'param' => 'transportManager'],
            'irhpApplication' => ['route' => 'licence/irhp-application-docs', 'param' => 'irhpAppId'],
            'irfoOrganisation' => ['route' => 'operator/documents', 'param' => 'organisation'],
        ];

        $type = $entityContext['type'];
        $id = $entityContext['id'];

        if (isset($documentRouteMap[$type])) {
            $config = $documentRouteMap[$type];
            return $this->redirect()->toRoute(
                $config['route'],
                [$config['param'] => $id, 'action' => 'index']
            );
        }

        return $this->redirect()->toRoute('dashboard');
    }
}
