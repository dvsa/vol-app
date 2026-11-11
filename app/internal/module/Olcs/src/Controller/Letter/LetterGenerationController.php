<?php

namespace Olcs\Controller\Letter;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Util\SafeRedirectUrl;
use Laminas\Http\Response;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * Letter Generation Controller
 * Handles database-driven letter creation workflow
 */
class LetterGenerationController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Left sidebar view for preview page
     */
    protected ?ViewModel $leftView = null;

    /**
     * Inline scripts for form handling
     */
    protected $inlineScripts = [
        'createAction' => ['forms/letter-generation'],
        'previewAction' => ['forms/letter-preview', 'forms/letter-change-issues'],
        'editAction' => ['forms/letter-edit', 'forms/letter-change-issues'],
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
        Navigation $navigation,
        private readonly int $csrfTimeout = 5400
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
    #[\Override]
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

        // Goods/PSV of the licence or application, null when there isn't one (TM, IRFO)
        $goodsOrPsv = $this->fetchLetterContext($entityContext)['goodsOrPsv'] ?? null;

        // Build accordion data structure with issue types and their issues
        $accordionData = $this->buildAccordionData($goodsOrPsv);

        // Fetch appendices for this letter type
        $appendicesData = $this->fetchAppendicesForLetterType($templateId);

        // Fetch letter choices for this letter type
        $letterChoicesData = $this->fetchLetterChoicesForLetterType($templateId, $goodsOrPsv);

        $view = new ViewModel([
            'templateId' => $templateId,
            'entityContext' => $entityContext,
            'accordionData' => $accordionData,
            'appendicesData' => $appendicesData,
            'letterChoicesData' => $letterChoicesData,
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
        $goodsOrPsv = $this->fetchLetterContext($entityContext)['goodsOrPsv'] ?? null;

        $commandData = [
            'letterType' => $letterTypeId,
            'selectedIssues' => $postData['letterIssues'] ?? [],
            'selectedAppendices' => $postData['letterAppendices'] ?? [],
        ];

        // Checkbox choices post as letterChoices[]; radio "pick one" groups post as
        // letterChoiceGroup[<groupIndex>] => choiceId. Merge both into selectedChoices.
        $radioSelections = array_values($postData['letterChoiceGroup'] ?? []);
        $selectedChoices = array_merge($postData['letterChoices'] ?? [], $radioSelections);

        // Enforce "pick exactly one" for every radio group on this letter type
        $radioError = $this->validateRequiredRadioChoices($templateId, $selectedChoices, $goodsOrPsv);
        if ($radioError !== null) {
            return $this->jsonError($radioError);
        }

        // Only include selectedChoices if any were actually selected
        if (!empty($selectedChoices)) {
            $commandData['selectedChoices'] = $selectedChoices;
        }

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

        // Extract section warnings if present. Optional sections are reported too: a section
        // dropping out of a letter is worth telling the caseworker about whether or not the
        // letter type marked it required.
        $warnings = [];
        if (!empty($result['flags']['hasRequiredSectionWarnings'])) {
            $warnings = array_values(array_filter(
                $result['messages'] ?? [],
                fn($m) => str_starts_with($m, 'Required section') || str_starts_with($m, 'Optional section')
            ));
        }

        return $this->jsonSuccess([
            'letterInstanceId' => $letterInstanceId,
            'message' => $result['messages'][0] ?? 'Letter generated successfully',
            'redirectUrl' => '#',
            'letterInstance' => $letterInstanceData,
            'warnings' => $warnings,
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
        $this->flashMessengerHelperService->clearCurrentMessagesFromContainer();

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
        // NOTE: despite the name, $sectionsList contains ISSUES (kept for backwards compatibility).
        $sectionsList = $this->buildIssuesList($result);

        // Add editable appendices to sections list
        $appendicesList = [];
        foreach ($result['letterInstanceAppendices'] ?? [] as $appendix) {
            $version = $appendix['letterAppendixVersion'] ?? [];
            if (($version['appendixType'] ?? 'pdf') === 'editable') {
                $appendicesList[] = [
                    'id' => $appendix['id'],
                    'name' => $version['name'] ?? 'Appendix',
                    'type' => 'appendix',
                ];
            }
        }

        // Build real letter instance sections list for the sidebar.
        // Each entry includes id, a display name, and position in assembly
        // so we can render checkboxes that let caseworkers edit them.
        $instanceSectionsList = [];
        foreach ($result['letterInstanceSections'] ?? [] as $instanceSection) {
            $sectionVersion = $instanceSection['letterSectionVersion'] ?? [];
            $variant = $sectionVersion['letterSectionVariant'] ?? [];
            $letterSection = $variant['letterSection'] ?? ($sectionVersion['letterSection'] ?? []);

            // Skip the __ISSUES__ placeholder section -- its content is rendered
            // from the letter's issues, not editable as a section.
            if (($letterSection['sectionKey'] ?? null) === '__ISSUES__') {
                continue;
            }

            $name = $sectionVersion['name']
                ?? $letterSection['name']
                ?? $letterSection['sectionKey']
                ?? null;

            if (empty($name)) {
                $name = 'Section #' . ($instanceSection['id'] ?? '');
            }

            $instanceSectionsList[] = [
                'id' => $instanceSection['id'] ?? null,
                'name' => $name,
                'position' => $instanceSection['positionInAssembly'] ?? 0,
                'type' => 'section',
                'inputPending' => !empty($sectionVersion['requiresInput'])
                    && empty($instanceSection['editedContent']),
            ];
        }

        $todosList = $this->buildTodosList($result);

        // Check for missing required sections
        $warnings = $this->checkRequiredSections($result);

        // Set left sidebar BEFORE calling viewBuilder (LeftViewProvider interface)
        $sidebarView = new ViewModel([
            'letterInstance' => $result,
            'sectionsList' => $sectionsList,
            'appendicesList' => $appendicesList,
            'instanceSectionsList' => $instanceSectionsList,
            'todosList' => $todosList,
        ]);
        $sidebarView->setTemplate('pages/letter/preview-sidebar');
        $this->leftView = $sidebarView;

        $view = new ViewModel([
            'letterInstanceId' => $letterInstanceId,
            'letterInstance' => $result,
            'sectionsList' => $sectionsList,
            'appendicesList' => $appendicesList,
            'instanceSectionsList' => $instanceSectionsList,
            'warnings' => $warnings,
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
    #[\Override]
    public function editAction()
    {
        $letterInstanceId = $this->params()->fromQuery('id');
        $selectedSections = $this->params()->fromQuery('sections', []);
        $selectedAppendices = $this->params()->fromQuery('appendices', []);
        $letterSectionIds = $this->params()->fromQuery('letterSections', []);
        $todoIds = $this->params()->fromQuery('todos', []);

        if (!$letterInstanceId) {
            $this->flashMessengerHelperService->addErrorMessage('Letter instance ID is required');
            return $this->redirect()->toRoute('dashboard');
        }

        if (
            empty($selectedSections) && empty($selectedAppendices)
            && empty($letterSectionIds) && empty($todoIds)
        ) {
            $this->flashMessengerHelperService->addErrorMessage('Nothing selected to edit');
            return $this->redirect()->toUrl('/letter/preview?id=' . urlencode($letterInstanceId));
        }

        $letterInstance = $this->fetchLetterInstanceById((int) $letterInstanceId);

        if (!$letterInstance) {
            $this->flashMessengerHelperService->addErrorMessage('Letter not found');
            return $this->redirect()->toRoute('dashboard');
        }

        // every save would be refused once it's sent (VOL-7735), so don't offer the editors
        if (empty($letterInstance['isOpenForChanges'])) {
            $this->flashMessengerHelperService->addErrorMessage(
                empty($letterInstance['isSent'])
                    ? 'This letter can no longer be changed'
                    : 'This letter has been sent, so it can no longer be changed'
            );
            return $this->redirect()->toUrl('/letter/preview?id=' . urlencode((string) $letterInstanceId));
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

            $effectiveContent = $this->editorContent(
                $issue['editedContent'] ?? null,
                $issue['generatedContent'] ?? null,
                $issueVersion['defaultBodyContent'] ?? null
            );

            $groupedIssues[$typeId]['issues'][] = [
                'id' => $issue['id'],
                'heading' => $issue['effectiveHeading'] ?? $issueVersion['heading'] ?? 'Issue',
                // an Other issue's heading is the caseworker's to write (VOL-7735)
                'isOther' => !empty($issue['isOther']),
                'editedHeading' => (string) ($issue['editedHeading'] ?? ''),
                'defaultHeading' => (string) ($issueVersion['heading'] ?? 'Other issue'),
                'content' => $effectiveContent,
                'version' => $issue['version'] ?? 1,
            ];
        }

        // Build editable appendix data
        $groupedAppendices = [];
        foreach ($letterInstance['letterInstanceAppendices'] ?? [] as $appendix) {
            if (!in_array($appendix['id'], $selectedAppendices)) {
                continue;
            }

            $appendixVersion = $appendix['letterAppendixVersion'] ?? [];
            if (($appendixVersion['appendixType'] ?? 'pdf') !== 'editable') {
                continue;
            }

            $effectiveContent = $this->editorContent(
                $appendix['editedContent'] ?? null,
                $appendix['generatedContent'] ?? null,
                $appendixVersion['defaultContent'] ?? null
            );

            $groupedAppendices[] = [
                'id' => $appendix['id'],
                'name' => $appendixVersion['name'] ?? 'Appendix',
                'content' => $effectiveContent,
                'version' => $appendix['version'] ?? 1,
            ];
        }

        // Build editable letter instance section data
        $groupedSections = [];
        foreach ($letterInstance['letterInstanceSections'] ?? [] as $instanceSection) {
            if (!in_array($instanceSection['id'], $letterSectionIds)) {
                continue;
            }

            $sectionVersion = $instanceSection['letterSectionVersion'] ?? [];
            $variant = $sectionVersion['letterSectionVariant'] ?? [];
            $letterSection = $variant['letterSection'] ?? ($sectionVersion['letterSection'] ?? []);

            // Skip the __ISSUES__ placeholder section -- not editable
            if (($letterSection['sectionKey'] ?? null) === '__ISSUES__') {
                continue;
            }

            $name = $sectionVersion['name']
                ?? $letterSection['name']
                ?? $letterSection['sectionKey']
                ?? null;

            if (empty($name)) {
                $name = 'Section #' . ($instanceSection['id'] ?? '');
            }

            $effectiveContent = $this->editorContent(
                $instanceSection['editedContent'] ?? null,
                $instanceSection['generatedContent'] ?? null,
                $sectionVersion['defaultContent'] ?? null
            );

            $groupedSections[] = [
                'id' => $instanceSection['id'],
                'name' => $name,
                'content' => $effectiveContent,
                'version' => $instanceSection['version'] ?? 1,
            ];
        }

        // Build editable to-do data.
        //
        // Unlike the three above, the fallback is the shared wording on the version -- there is
        // no per-instance default. requiringIssueCount comes from the API and drives the note
        // telling the caseworker that a shared to-do is standing in for several issues.
        $groupedTodos = [];
        foreach ($letterInstance['letterInstanceTodos'] ?? [] as $todo) {
            if (!in_array($todo['id'], $todoIds)) {
                continue;
            }

            $todoVersion = $todo['letterTodoVersion'] ?? [];

            $effectiveContent = $this->editorContent(
                $todo['editedDescription'] ?? null,
                $todo['generatedDescription'] ?? null,
                $todoVersion['description'] ?? null
            );

            $groupedTodos[] = [
                'id' => $todo['id'],
                'name' => $this->todoEditorName($todo),
                'isCustom' => !empty($todo['isCustom']),
                'customLabel' => $todo['customLabel'] ?? null,
                'content' => $effectiveContent,
                'version' => $todo['version'] ?? 1,
                'requiringIssueCount' => (int) ($todo['requiringIssueCount'] ?? 1),
                'placementIssueIds' => $todo['placementIssueIds'] ?? [],
            ];
        }

        // The page follows the letter (VOL-7737): sections around the issues block as they sit
        // in the letter, each issue followed by its own to-dos.
        [$groupedIssues, $groupedTodos] = $this->placeTodosUnderIssues($groupedIssues, $groupedTodos);

        $view = new ViewModel([
            'letterInstanceId' => $letterInstanceId,
            'letterInstance' => $letterInstance,
            'layout' => $this->editLayout($letterInstance['letterInstanceSections'] ?? [], $groupedSections),
            'groupedIssues' => $groupedIssues,
            'groupedAppendices' => $groupedAppendices,
            'groupedTodos' => $groupedTodos,
        ]);

        $view->setTemplate('pages/letter/edit');

        // Set navigation for breadcrumbs
        $this->navigationId = 'letter_edit';
        $this->setNavigationCurrentLocation();

        $this->placeholder()->setPlaceholder('pageTitle', 'Edit letter sections');

        return $this->viewBuilder()->buildView($view);
    }

    /**
     * Change issues action - the issue picker for a letter that has already been generated (VOL-7734)
     *
     * Rendered on its own for a modal on the preview and edit pages. Offers the same issues as
     * creating the letter, with those already on it ticked. An issue on the letter that is no longer
     * offered (switched off since) is still listed and ticked, so saving doesn't quietly drop it.
     *
     * Query parameters: id (letter instance), from ('preview' or 'edit')
     *
     * @return ViewModel|Response
     */
    public function changeIssuesAction()
    {
        $letterInstanceId = (int) $this->params()->fromQuery('id');
        $letterInstance = $letterInstanceId > 0 ? $this->fetchLetterInstanceById($letterInstanceId) : null;

        if ($letterInstance === null) {
            $response = $this->getResponse();
            $response->setStatusCode(404);
            $response->setContent('<p class="govuk-body">Letter not found</p>');
            return $response;
        }

        // the letter's own rule, worked out by the API (LetterInstance::isOpenForChanges)
        $locked = empty($letterInstance['isOpenForChanges']);

        $selectedIssueIds = [];
        $accordionData = [];
        if (!$locked) {
            $goodsOrPsv = $this->fetchLetterContext($this->entityContextOf($letterInstance))['goodsOrPsv'] ?? null;
            $accordionData = $this->buildAccordionData($goodsOrPsv);
            [$accordionData, $selectedIssueIds] = $this->withIssuesOnLetter($accordionData, $letterInstance);
        }

        $view = new ViewModel([
            'letterInstanceId' => $letterInstanceId,
            'from' => $this->params()->fromQuery('from') === 'edit' ? 'edit' : 'preview',
            'locked' => $locked,
            'hasAppendices' => !empty($letterInstance['letterInstanceAppendices']),
            'accordionData' => $accordionData,
            'selectedIssueIds' => $selectedIssueIds,
            'csrfToken' => $locked ? '' : $this->csrfToken(),
        ]);
        $view->setTemplate('pages/letter/change-issues');
        $view->setTerminal(true);

        return $view;
    }

    /**
     * Update issues action - saves the issues picked in the change issues modal (VOL-7734)
     *
     * Posts the full list of ticked issues. When that would throw away the caseworker's edits the
     * API changes nothing and names them; the modal asks, then posts again with confirmLoss.
     *
     * POST: letterInstanceId, letterIssues[] (in the order shown), confirmLoss ('1')
     *
     * @return Response JSON
     */
    public function updateIssuesAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $this->jsonError('Method not allowed', 405);
        }

        $letterInstanceId = (int) $request->getPost('letterInstanceId');
        $command = \Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\UpdateIssues::create([
            'id' => $letterInstanceId,
            'selectedIssues' => array_map('intval', array_values((array) $request->getPost('letterIssues', []))),
            'confirmLoss' => $request->getPost('confirmLoss') === '1',
        ]);

        $response = $this->handleCommand($command);
        $result = $response->getResult();

        if (!$response->isOk()) {
            $messages = [];
            $raw = (array) ($result['messages'] ?? []);
            array_walk_recursive($raw, static function ($message) use (&$messages): void {
                if (is_string($message)) {
                    $messages[] = $message;
                }
            });
            return $this->jsonError($messages === [] ? 'Failed to change issues' : implode(' ', $messages));
        }

        if (!empty($result['flags']['confirmationRequired'])) {
            return $this->jsonSuccess([
                'confirmationRequired' => true,
                'lostEdits' => array_values($result['messages'] ?? []),
            ]);
        }

        $letterInstance = $this->fetchLetterInstanceById($letterInstanceId) ?? [];

        return $this->jsonSuccess([
            'addedIssueIds' => array_values($result['flags']['addedIssueIds'] ?? []),
            'issueIds' => array_column($letterInstance['letterInstanceIssues'] ?? [], 'id'),
            'todoIds' => array_column($letterInstance['letterInstanceTodos'] ?? [], 'id'),
            // old to-do id => new, for to-dos carried over to another issue as a new row
            'movedTodoIds' => (object) ($result['flags']['movedTodoIds'] ?? []),
        ]);
    }

    /**
     * A "security" token for a hand-built form. Common\Module turns form posts without a valid one
     * into GETs; the timeout matches the forms the app builds itself, not the validator's 5 minutes.
     */
    protected function csrfToken(): string
    {
        $element = new \Laminas\Form\Element\Csrf('security', [
            'csrf_options' => ['timeout' => $this->csrfTimeout],
        ]);

        return (string) $element->getValue();
    }

    /**
     * The entity Goods/PSV is worked out from, the same way Generate does: application, then licence
     *
     * @return array{type?: string, id?: int}
     */
    protected function entityContextOf(array $letterInstance): array
    {
        foreach (['application', 'licence'] as $type) {
            if (!empty($letterInstance[$type]['id'])) {
                return ['type' => $type, 'id' => (int) $letterInstance[$type]['id']];
            }
        }

        return [];
    }

    /**
     * Tick the issues already on the letter, adding any the picker no longer offers to their type
     * (or a group of their own at the end, if the type has gone too)
     *
     * @return array{0: array, 1: list<int>} accordion data, selected LetterIssue ids
     */
    protected function withIssuesOnLetter(array $accordionData, array $letterInstance): array
    {
        $offered = [];
        foreach ($accordionData as $section) {
            foreach ($section['issues'] as $issue) {
                $offered[(int) $issue['id']] = true;
            }
        }

        $selected = [];
        foreach ($letterInstance['letterInstanceIssues'] ?? [] as $instanceIssue) {
            $version = $instanceIssue['letterIssueVersion'] ?? [];
            $issueId = (int) ($version['letterIssue']['id'] ?? 0);
            if ($issueId === 0 || in_array($issueId, $selected, true)) {
                continue;
            }
            $selected[] = $issueId;

            if (isset($offered[$issueId])) {
                continue;
            }

            $missing = [
                'id' => $issueId,
                'issueKey' => '',
                'currentVersion' => ['heading' => $version['heading'] ?? 'Issue', 'isOther' => !empty($version['isOther'])],
            ];
            $type = $version['letterIssueType'] ?? ['id' => 0, 'name' => 'Other issues'];

            $placed = false;
            foreach ($accordionData as $index => $section) {
                if ((int) ($section['issueType']['id'] ?? -1) === (int) ($type['id'] ?? 0)) {
                    // before the type's Other issue, which stays last (VOL-7735): the letter is
                    // numbered in the order shown
                    $at = count($section['issues']);
                    if (!$missing['currentVersion']['isOther']) {
                        foreach ($section['issues'] as $position => $offeredIssue) {
                            if (!empty($offeredIssue['currentVersion']['isOther'])) {
                                $at = $position;
                                break;
                            }
                        }
                    }
                    array_splice($accordionData[$index]['issues'], $at, 0, [$missing]);
                    $placed = true;
                    break;
                }
            }
            if (!$placed) {
                $accordionData[] = ['issueType' => $type, 'issues' => [$missing]];
            }
            $offered[$issueId] = true;
        }

        return [$accordionData, $selected];
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
        $body = $this->jsonPostBody();
        if ($body instanceof Response) {
            return $body;
        }

        if (empty($body['issueId']) || !isset($body['editedContent']) || empty($body['version'])) {
            return $this->jsonError('Missing required fields: issueId, editedContent, version');
        }

        $data = [
            'id' => (int) $body['issueId'],
            'editedContent' => is_string($body['editedContent'])
                ? $body['editedContent']
                : json_encode($body['editedContent']),
            'version' => (int) $body['version'],
        ];
        // an Other issue's heading travels with its body (VOL-7735)
        if (array_key_exists('editedHeading', $body)) {
            $data['editedHeading'] = (string) $body['editedHeading'];
        }

        $command = \Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceIssue\UpdateContent::create($data);

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
     * Save appendix content action - AJAX endpoint
     *
     * Accepts POST with JSON body: { appendixId, editedContent, version }
     *
     * @return Response
     */
    public function saveAppendixContentAction()
    {
        $body = $this->jsonPostBody();
        if ($body instanceof Response) {
            return $body;
        }

        if (empty($body['appendixId']) || !isset($body['editedContent']) || empty($body['version'])) {
            return $this->jsonError('Missing required fields: appendixId, editedContent, version');
        }

        $command = \Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceAppendix\UpdateContent::create([
            'id' => (int) $body['appendixId'],
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
            'appendixId' => (int) $body['appendixId'],
            'message' => $result['messages'][0] ?? 'Saved successfully',
            'version' => ($body['version'] + 1),
        ]);
    }

    /**
     * Add an empty caseworker-written to-do to an Other issue - AJAX endpoint (VOL-7735)
     *
     * Accepts POST with JSON body: { issueId }; returns { todoId }
     *
     * @return Response
     */
    public function addCustomTodoAction()
    {
        $body = $this->jsonPostBody();
        if ($body instanceof Response) {
            return $body;
        }
        if (empty($body['issueId'])) {
            return $this->jsonError('Missing required field: issueId');
        }

        $response = $this->handleCommand(\Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo\CreateCustom::create([
            'letterInstanceIssue' => (int) $body['issueId'],
        ]));

        if (!$response->isOk()) {
            return $this->jsonError('Failed to add the to-do: ' . $this->commandMessages($response));
        }

        return $this->jsonSuccess(['todoId' => (int) ($response->getResult()['id']['letterInstanceTodo'] ?? 0)]);
    }

    /**
     * Delete a caseworker-written to-do - AJAX endpoint (VOL-7735)
     *
     * Accepts POST with JSON body: { todoId }
     *
     * @return Response
     */
    public function deleteCustomTodoAction()
    {
        $body = $this->jsonPostBody();
        if ($body instanceof Response) {
            return $body;
        }
        if (empty($body['todoId'])) {
            return $this->jsonError('Missing required field: todoId');
        }

        $response = $this->handleCommand(\Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo\DeleteCustom::create([
            'id' => (int) $body['todoId'],
        ]));

        if (!$response->isOk()) {
            return $this->jsonError('Failed to delete the to-do: ' . $this->commandMessages($response));
        }

        return $this->jsonSuccess(['todoId' => (int) $body['todoId']]);
    }

    private function commandMessages(\Common\Service\Cqrs\Response $response): string
    {
        $messages = $response->getResult()['messages'] ?? [];
        $flat = [];
        array_walk_recursive($messages, static function ($message) use (&$flat): void {
            $flat[] = (string) $message;
        });

        return implode(', ', $flat);
    }

    /**
     * Save a caseworker's edit to one to-do's wording.
     *
     * The field is editedDescription, not editedContent like the three siblings: a to-do's
     * wording is called `description` throughout its own code path, so the override is named
     * after what it overrides.
     */
    public function saveTodoContentAction()
    {
        $body = $this->jsonPostBody();
        if ($body instanceof Response) {
            return $body;
        }

        if (empty($body['todoId']) || !isset($body['editedDescription']) || empty($body['version'])) {
            return $this->jsonError('Missing required fields: todoId, editedDescription, version');
        }

        $command = \Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo\UpdateContent::create([
            'id' => (int) $body['todoId'],
            'editedDescription' => is_string($body['editedDescription'])
                ? $body['editedDescription']
                : json_encode($body['editedDescription']),
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
            'todoId' => (int) $body['todoId'],
            'message' => $result['messages'][0] ?? 'Saved successfully',
            'version' => ($body['version'] + 1),
        ]);
    }

    /**
     * Save section content action - AJAX endpoint
     *
     * Accepts POST with JSON body: { sectionId, editedContent, version }
     *
     * @return Response
     */
    public function saveSectionContentAction()
    {
        $body = $this->jsonPostBody();
        if ($body instanceof Response) {
            return $body;
        }

        if (empty($body['sectionId']) || !isset($body['editedContent']) || empty($body['version'])) {
            return $this->jsonError('Missing required fields: sectionId, editedContent, version');
        }

        $command = \Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceSection\UpdateContent::create([
            'id' => (int) $body['sectionId'],
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
            'sectionId' => (int) $body['sectionId'],
            'message' => $result['messages'][0] ?? 'Saved successfully',
            'version' => ($body['version'] + 1),
        ]);
    }

    /**
     * Prepare to send action - converts letter to PDF and creates Document
     *
     * Accepts POST with JSON body: { letterInstanceId, docTemplate }
     * Returns JSON: { success, documentId, printUrl }
     *
     * @return Response
     */
    public function prepareToSendAction()
    {
        $body = $this->jsonPostBody();
        if ($body instanceof Response) {
            return $body;
        }

        if (empty($body['letterInstanceId']) || empty($body['docTemplate'])) {
            return $this->jsonError('Missing required fields: letterInstanceId, docTemplate');
        }

        $command = \Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\PrepareToSend::create([
            'id' => (int) $body['letterInstanceId'],
            'docTemplate' => (int) $body['docTemplate'],
        ]);

        $response = $this->handleCommand($command);

        if (!$response->isOk()) {
            // Validation failures arrive keyed and nested (field => [message, ...]),
            // so flatten before joining or the user sees the literal string "Array".
            $messages = (array) ($response->getResult()['messages'] ?? []);
            $flatMessages = [];
            array_walk_recursive(
                $messages,
                static function ($message) use (&$flatMessages): void {
                    $flatMessages[] = (string) $message;
                }
            );
            return $this->jsonError('Failed to prepare letter: ' . implode(', ', $flatMessages));
        }

        $result = $response->getResult();
        $documentId = $result['id']['document'] ?? null;

        if (!$documentId) {
            return $this->jsonError('Document was not created');
        }

        // Fetch the letter instance to determine entity context for the printAction URL
        $letterInstance = $this->fetchLetterInstanceById((int) $body['letterInstanceId']);

        // Build the printAction URL for the entity-specific finalise route
        $printUrl = $this->buildPrintActionUrl($letterInstance, $documentId);

        return $this->jsonSuccess([
            'documentId' => $documentId,
            'printUrl' => $printUrl,
        ]);
    }

    /**
     * What the editor opens with: the caseworker's edit, else the content resolved at generation,
     * else the version default. Each may be an array or a JSON string from the API.
     */
    protected function editorContent(mixed ...$candidates): string
    {
        foreach ($candidates as $candidate) {
            if (empty($candidate)) {
                continue;
            }

            return is_string($candidate) ? $candidate : json_encode($candidate);
        }

        return json_encode(['blocks' => [], 'version' => '2.28.2']);
    }

    /**
     * Put each to-do under the first issue it can go under that is on the edit page
     * (placementIssueIds comes from the API, preferred first). To-dos with none of their issues
     * on the page are returned separately, to be listed after the issues.
     *
     * @param array<int|string, array{issues: list<array<string, mixed>>}> $groupedIssues
     * @param list<array<string, mixed>> $todos
     * @return array{0: array<int|string, array<string, mixed>>, 1: list<array<string, mixed>>}
     */
    protected function placeTodosUnderIssues(array $groupedIssues, array $todos): array
    {
        $issueSlots = [];
        foreach ($groupedIssues as $typeId => $group) {
            foreach ($group['issues'] as $index => $issue) {
                $groupedIssues[$typeId]['issues'][$index]['todos'] = [];
                $issueSlots[(int) $issue['id']] = [$typeId, $index];
            }
        }

        $unplaced = [];
        foreach ($todos as $todo) {
            $slot = null;
            foreach ($todo['placementIssueIds'] ?? [] as $issueId) {
                if (isset($issueSlots[(int) $issueId])) {
                    $slot = $issueSlots[(int) $issueId];
                    break;
                }
            }

            if ($slot === null) {
                // listed away from its issue, so a custom to-do keeps the issue's name (VOL-7735)
                if (!empty($todo['isCustom']) && !empty($todo['customLabel'])) {
                    $todo['name'] = $todo['customLabel'];
                }
                $unplaced[] = $todo;
                continue;
            }

            [$typeId, $index] = $slot;
            $groupedIssues[$typeId]['issues'][$index]['todos'][] = $todo;
        }

        return [$groupedIssues, $unplaced];
    }

    /**
     * The order of the edit page, matching the letter: the chosen sections in letter order, with
     * the issues block where the letter's __ISSUES__ section sits, or after the sections when the
     * letter has none (as the letter itself does).
     *
     * @param list<array<string, mixed>> $instanceSections the letter's sections, in letter order
     * @param list<array<string, mixed>> $editableSections the chosen sections, each with an id
     * @return list<array{section: array<string, mixed>}|array{issues: true}>
     */
    protected function editLayout(array $instanceSections, array $editableSections): array
    {
        $byId = [];
        foreach ($editableSections as $section) {
            $byId[$section['id']] = $section;
        }

        $layout = [];
        $issuesPlaced = false;
        foreach ($instanceSections as $instanceSection) {
            $sectionVersion = $instanceSection['letterSectionVersion'] ?? [];
            $letterSection = $sectionVersion['letterSectionVariant']['letterSection'] ?? ($sectionVersion['letterSection'] ?? []);

            if (($letterSection['sectionKey'] ?? null) === '__ISSUES__' && !$issuesPlaced) {
                $layout[] = ['issues' => true];
                $issuesPlaced = true;
            } elseif (isset($byId[$instanceSection['id']])) {
                $layout[] = ['section' => $byId[$instanceSection['id']]];
            }
        }

        if (!$issuesPlaced) {
            $layout[] = ['issues' => true];
        }

        return $layout;
    }

    /**
     * Build the print action URL for the document finalise controller
     *
     * @param array|null $letterInstance Letter instance data
     * @param int $documentId Document ID
     * @return string The print action URL
     */
    protected function buildPrintActionUrl(?array $letterInstance, int $documentId): string
    {
        if (!$letterInstance) {
            return '/documents/finalise/' . $documentId . '/print';
        }

        // Determine entity context from the letter instance
        $entityRouteMap = [
            'licence' => ['route' => 'licence/documents/finalise', 'param' => 'licence', 'key' => 'licence'],
            'application' => ['route' => 'lva-application/documents/finalise', 'param' => 'application', 'key' => 'application'],
            'case' => ['route' => 'case_licence_docs_attachments/finalise', 'param' => 'case', 'key' => 'case'],
            'busReg' => ['route' => 'licence/bus-docs/finalise', 'param' => 'busRegId', 'key' => 'busReg'],
            'transportManager' => ['route' => 'transport-manager/documents/finalise', 'param' => 'transportManager', 'key' => 'transportManager'],
            'irhpApplication' => ['route' => 'licence/irhp-application-docs/finalise', 'param' => 'irhpAppId', 'key' => 'irhpApplication'],
        ];

        foreach ($entityRouteMap as $entityType => $config) {
            $entityData = $letterInstance[$config['key']] ?? null;
            if (!empty($entityData)) {
                $entityId = is_array($entityData) ? ($entityData['id'] ?? null) : $entityData;
                if ($entityId) {
                    try {
                        return $this->url()->fromRoute(
                            $config['route'],
                            [
                                $config['param'] => $entityId,
                                'doc' => $documentId,
                                'action' => 'print',
                            ]
                        );
                    } catch (\Exception $e) {
                        // Fall through to fallback
                    }
                }
            }
        }

        // Fallback: construct URL manually
        return '/documents/finalise/' . $documentId . '/print';
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
     * The JSON body of an AJAX post, or the error response to send instead.
     *
     * These endpoints read the raw body, so Common\Module's CSRF check, which only looks at form
     * posts, never applies to them. Insisting on a JSON content type closes that gap: another site
     * can't send one without a CORS preflight, and this app doesn't allow cross-site requests.
     *
     * @return array<string, mixed>|Response
     */
    private function jsonPostBody(): array|Response
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $this->jsonError('Method not allowed', 405);
        }

        $contentType = $request->getHeaders()->get('Content-Type');
        $mediaType = $contentType instanceof \Laminas\Http\Header\HeaderInterface
            ? strtolower(trim(explode(';', $contentType->getFieldValue())[0]))
            : '';
        if ($mediaType !== 'application/json') {
            return $this->jsonError('Expected a JSON request', 415);
        }

        $body = json_decode($request->getContent(), true);

        return is_array($body) ? $body : [];
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
     * @param string|null $goodsOrPsv Letter's Goods/PSV, null shows every issue
     * @return array Array of ['issueType' => [...], 'issues' => [...]]
     */
    protected function buildAccordionData(?string $goodsOrPsv = null): array
    {
        // Fetch all active issue types ordered by display order
        $issueTypes = $this->fetchActiveIssueTypes();

        // Fetch all active letter issues
        $letterIssues = $this->fetchActiveLetterIssues();

        // Group issues by issue type ID
        $issuesByType = [];
        foreach ($letterIssues as $issue) {
            if (!$this->appliesToGoodsOrPsv($issue['currentVersion']['goodsOrPsv'] ?? null, $goodsOrPsv)) {
                continue;
            }

            $typeId = $issue['currentVersion']['letterIssueType']['id'] ?? null;
            if ($typeId) {
                if (!isset($issuesByType[$typeId])) {
                    $issuesByType[$typeId] = [];
                }
                $issuesByType[$typeId][] = $issue;
            }
        }

        // Build final structure. The Other option goes last in its group (VOL-7735): its key
        // ("FI-OTHER") sorts before the numbered ones, as "-" comes before the digits.
        $accordionData = [];
        foreach ($issueTypes as $issueType) {
            $issues = $issuesByType[$issueType['id']] ?? [];
            $isOther = static fn(array $issue): bool => !empty($issue['currentVersion']['isOther']);
            $accordionData[] = [
                'issueType' => $issueType,
                'issues' => array_merge(
                    array_values(array_filter($issues, static fn(array $issue): bool => !$isOther($issue))),
                    array_values(array_filter($issues, $isOther))
                ),
            ];
        }

        return $accordionData;
    }

    /**
     * Goods/PSV and NI of the entity the letter is for, worked out the same way generation does
     *
     * @param array $entityContext Entity context with type and ID
     * @return array ['goodsOrPsv' => ?string, 'isNi' => ?bool], empty when there is no entity
     */
    protected function fetchLetterContext(array $entityContext): array
    {
        if (empty($entityContext['type'])) {
            return [];
        }

        $query = \Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\GenerationContext::create([
            $entityContext['type'] => $entityContext['id'],
        ]);

        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            return [];
        }

        return $response->getResult();
    }

    /**
     * Whether an issue or choice set to Goods, PSV or neither (both) belongs on this letter
     *
     * @param array|string|null $setting Serialised RefData or just its id
     * @param string|null $goodsOrPsv Letter's Goods/PSV, null when unknown
     * @return bool
     */
    protected function appliesToGoodsOrPsv(array|string|null $setting, ?string $goodsOrPsv): bool
    {
        $settingId = is_array($setting) ? ($setting['id'] ?? null) : $setting;

        return $goodsOrPsv === null || empty($settingId) || $settingId === $goodsOrPsv;
    }

    /**
     * Fetch active issue types ordered by display order
     *
     * @return array
     */
    protected function fetchActiveIssueTypes(): array
    {
        $results = $this->fetchAllPages(
            \Dvsa\Olcs\Transfer\Query\Letter\LetterIssueType\GetList::class,
            ['sort' => 'displayOrder', 'order' => 'ASC']
        );

        // Filter active issue types only
        $issueTypes = array_filter($results, fn($issueType) => !empty($issueType['isActive']));

        return array_values($issueTypes);
    }

    /**
     * Fetch all active letter issues with their current version data
     *
     * @return array
     */
    protected function fetchActiveLetterIssues(): array
    {
        return $this->fetchAllPages(
            \Dvsa\Olcs\Transfer\Query\Letter\LetterIssue\GetList::class,
            ['sort' => 'issueKey', 'order' => 'ASC']
        );
    }

    /**
     * Fetch every row of a list query, a page at a time
     *
     * @param string $listQueryClass Paged list query DTO class
     * @param array $params Sort and order
     * @return array
     */
    protected function fetchAllPages(string $listQueryClass, array $params): array
    {
        $results = [];
        $page = 1;

        do {
            // 100 is the most a list query allows per page
            $response = $this->handleQuery($listQueryClass::create($params + ['page' => $page++, 'limit' => 100]));

            if (!$response->isOk()) {
                break;
            }

            $result = $response->getResult();
            $pageResults = $result['results'] ?? [];
            $results = array_merge($results, $pageResults);
        } while (!empty($pageResults) && count($results) < (int) ($result['count'] ?? 0));

        return $results;
    }

    /**
     * Fetch appendices available for a letter type
     *
     * @param int $templateId Doc template ID
     * @return array Array of appendix data
     */
    protected function fetchAppendicesForLetterType(int $templateId): array
    {
        // Resolve doc template to letter type ID
        $template = $this->fetchTemplateById($templateId);

        if (!$template || empty($template['letterType']['id'])) {
            return [];
        }

        $letterTypeId = (int) $template['letterType']['id'];

        // Fetch the letter type to get its assigned appendices
        $query = \Dvsa\Olcs\Transfer\Query\Letter\LetterType\Get::create([
            'id' => $letterTypeId,
        ]);

        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            return [];
        }

        $result = $response->getResult();
        $appendices = [];

        foreach ($result['letterTypeAppendices'] ?? [] as $lta) {
            $version = $lta['letterAppendixVersion'] ?? [];
            $letterAppendix = $version['letterAppendix'] ?? [];
            $appendices[] = [
                'id' => $letterAppendix['id'] ?? null,
                'name' => $version['name'] ?? 'Appendix',
                'appendixType' => $version['appendixType'] ?? 'pdf',
                'isMandatory' => !empty($lta['isMandatory']),
            ];
        }

        return $appendices;
    }

    /**
     * Fetch letter choices linked to a letter type
     *
     * @param int $templateId Doc template ID
     * @param string|null $goodsOrPsv Letter's Goods/PSV, null offers every choice
     * @return array Letter choices data [{id, label, groupLabel, inputType}]
     */
    protected function fetchLetterChoicesForLetterType(int $templateId, ?string $goodsOrPsv = null): array
    {
        $template = $this->fetchTemplateById($templateId);

        if (!$template || empty($template['letterType']['id'])) {
            return [];
        }

        $letterTypeId = (int) $template['letterType']['id'];

        $query = \Dvsa\Olcs\Transfer\Query\Letter\LetterType\Get::create([
            'id' => $letterTypeId,
        ]);

        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            return [];
        }

        $result = $response->getResult();
        $choices = [];

        foreach ($result['letterTypeChoices'] ?? [] as $ltc) {
            $letterChoice = $ltc['letterChoice'] ?? [];
            if (
                !empty($letterChoice['isActive'])
                && $this->appliesToGoodsOrPsv($letterChoice['goodsOrPsv'] ?? null, $goodsOrPsv)
            ) {
                $choices[] = [
                    'id' => $letterChoice['id'] ?? null,
                    'label' => $letterChoice['label'] ?? '',
                    'groupLabel' => $letterChoice['groupLabel'] ?? 'Other letter choices',
                    'inputType' => $letterChoice['inputType'] ?? 'checkbox',
                    'displayOrder' => (int) ($letterChoice['displayOrder'] ?? 0),
                ];
            }
        }

        // VOL-7282: honour the admin-configured ordering (label as tiebreak)
        usort(
            $choices,
            fn(array $a, array $b) => [$a['displayOrder'], $a['label']] <=> [$b['displayOrder'], $b['label']]
        );

        return $choices;
    }

    /**
     * Validate that every radio "pick one" group on the letter type has exactly one option selected.
     *
     * Radio groups are mandatory by rule (no default, must pick one). Checkbox choices are optional
     * and not validated here. Mirrors the client-side guard so the rule holds if JS is bypassed.
     *
     * @param int $templateId Doc template ID
     * @param array $selectedChoices Selected letter choice IDs (checkbox + radio merged)
     * @param string|null $goodsOrPsv Letter's Goods/PSV, so a group that wasn't shown isn't demanded
     * @return string|null Error message if a radio group is unsatisfied, otherwise null
     */
    protected function validateRequiredRadioChoices(
        int $templateId,
        array $selectedChoices,
        ?string $goodsOrPsv = null
    ): ?string {
        $radioGroups = [];
        foreach ($this->fetchLetterChoicesForLetterType($templateId, $goodsOrPsv) as $choice) {
            if (($choice['inputType'] ?? 'checkbox') === 'radio') {
                $radioGroups[$choice['groupLabel']][] = (int) $choice['id'];
            }
        }

        $selectedIds = array_map('intval', $selectedChoices);

        foreach ($radioGroups as $groupLabel => $optionIds) {
            if (count(array_intersect($optionIds, $selectedIds)) !== 1) {
                return sprintf('Select one option for "%s"', $groupLabel);
            }
        }

        return null;
    }

    /**
     * Build the to-dos list for the preview sidebar.
     *
     * A to-do carries no name of its own on the instance row -- the label lives on the
     * version, and the key on the parent. Both are needed: two to-dos can share a name
     * (FI01 and FI02 both read "You need to upload bank statements to your online account"),
     * so the key is what makes them tellable apart.
     *
     * @param array $letterInstanceData Letter instance query result
     */
    protected function buildTodosList(array $letterInstanceData): array
    {
        $todosList = [];
        foreach ($letterInstanceData['letterInstanceTodos'] ?? [] as $todo) {
            $todosList[] = [
                'id' => $todo['id'] ?? null,
                // away from its issue a custom to-do carries the issue's name; the API words it (VOL-7735)
                'name' => (string) ($todo['customLabel'] ?? $this->todoEditorName($todo)),
                'type' => 'todo',
                'requiringIssueCount' => (int) ($todo['requiringIssueCount'] ?? 1),
                // must be written before the letter can be sent (PrepareToSend); the API decides
                'inputPending' => (bool) ($todo['inputPending'] ?? false),
            ];
        }

        return $todosList;
    }

    /**
     * The issues for the preview sidebar, named as the letter prints them (an Other issue by the
     * caseworker's heading, VOL-7735) and flagged when they still need input (VOL-7402).
     *
     * @param array $letterInstanceData Letter instance query result
     * @return list<array{id: mixed, name: string, type: string, inputPending: bool}>
     */
    protected function buildIssuesList(array $letterInstanceData): array
    {
        $issuesList = [];
        foreach ($letterInstanceData['letterInstanceIssues'] ?? [] as $issue) {
            $issuesList[] = [
                'id' => $issue['id'],
                'name' => (string) ($issue['displayName'] ?? $issue['effectiveHeading'] ?? $issue['letterIssueVersion']['heading'] ?? 'Issue'),
                'type' => 'issue',
                'inputPending' => (bool) ($issue['inputPending'] ?? false),
            ];
        }

        return $issuesList;
    }

    /**
     * A to-do's name on the edit page: a library to-do by its name and key (two can share a
     * name), a caseworker-written one by its place under its issue (VOL-7735).
     */
    protected function todoEditorName(array $todo): string
    {
        if (!empty($todo['isCustom'])) {
            return 'To-do ' . (int) ($todo['customIndex'] ?? 0);
        }

        $todoVersion = $todo['letterTodoVersion'] ?? [];
        $todoKey = $todoVersion['letterTodo']['todoKey'] ?? null;
        $name = $todoVersion['name'] ?? null;

        // Falls back to the key alone while the name backfill has not run.
        return $name === null ? ($todoKey ?? 'To-do') : trim($name . ' (' . $todoKey . ')');
    }

    /**
     * Check for required sections missing from the letter instance
     *
     * Compares the letter type's required sections against the instance's actual sections.
     *
     * @param array $letterInstanceData Letter instance query result
     * @return array Warning strings for each missing required section
     */
    protected function checkRequiredSections(array $letterInstanceData): array
    {
        $letterTypeId = $letterInstanceData['letterType']['id'] ?? null;
        if (!$letterTypeId) {
            return [];
        }

        $query = \Dvsa\Olcs\Transfer\Query\Letter\LetterType\Get::create([
            'id' => (int) $letterTypeId,
        ]);

        $response = $this->handleQuery($query);
        if (!$response->isOk()) {
            return [];
        }

        $letterType = $response->getResult();

        // Build set of section IDs present in the instance
        $instanceSectionIds = [];
        foreach ($letterInstanceData['letterInstanceSections'] ?? [] as $instanceSection) {
            $version = $instanceSection['letterSectionVersion'] ?? [];
            $variant = $version['letterSectionVariant'] ?? [];
            $sectionId = $variant['letterSection']['id'] ?? $version['letterSection']['id'] ?? null;
            if ($sectionId) {
                $instanceSectionIds[$sectionId] = true;
            }
        }

        // Check each required section
        $warnings = [];
        foreach ($letterType['letterTypeSections'] ?? [] as $typeSection) {
            if (empty($typeSection['isRequired'])) {
                continue;
            }

            $sectionId = $typeSection['letterSection']['id'] ?? null;
            $sectionName = $typeSection['letterSection']['name']
                ?? $typeSection['letterSection']['sectionKey']
                ?? 'Unknown section';

            if ($sectionId && !isset($instanceSectionIds[$sectionId])) {
                $warnings[] = $sectionName;
            }
        }

        return $warnings;
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
        // returnUrl arrives from the query string, so it is attacker-controlled. Only a
        // root-relative path is accepted: DocumentGenerationController sets it from
        // Request::getRequestUri(), which is always a path, so nothing legitimate is lost.
        // An unchecked redirect here is a phishing primitive inside the authenticated app.
        if (isset($queryParams['returnUrl']) && SafeRedirectUrl::isSafePath((string) $queryParams['returnUrl'])) {
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
