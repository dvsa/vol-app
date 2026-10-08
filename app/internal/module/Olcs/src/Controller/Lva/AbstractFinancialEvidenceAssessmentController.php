<?php

declare(strict_types=1);

namespace Olcs\Controller\Lva;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Controller\Lva\AbstractController;
use Common\FeatureToggle;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Document\AcceptDocumentAnalysisReview;
use Dvsa\Olcs\Transfer\Command\Document\OverrideDocumentAnalysisFlag;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentAnalysisAssessmentStatus;
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;
use Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\FormInterface;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Data\Mapper\FinancialEvidenceAssessmentReview as ReviewPageMapper;
use Olcs\Data\Mapper\FinancialEvidenceAssessmentTab;
use Olcs\Form\Model\Form\Lva\FinancialEvidenceAssessmentReview;
use Olcs\Form\Model\Form\Lva\FinancialEvidenceDecision;
use Olcs\Form\Model\Form\Lva\FinancialEvidenceIssueOverride;

/**
 * Financial evidence assessment page, shared by the licence, application and variation sections.
 *
 * Tabs are driven solely by successful document analyses. The concrete controllers only supply
 * the LVA context (via their trait and $lva), which decides whether analyses are scoped by
 * licence or by application. What each tab shows is decided by FinancialEvidenceAssessmentTab.
 *
 * Each tab can be reviewed by the caseworker through the FinancialEvidenceAssessmentReview form,
 * which posts back to this page. Its CSRF element is added by the form helper like any other form.
 * Accepting the review sends AcceptDocumentAnalysisReview; the API decides and records the outcome
 * from the stored result, like Application\Grant, and this page only reports it.
 *
 * "Change document review" opens changeReviewAction() on the section's action route. There the
 * caseworker can change failed or skipped checks to passes (OverrideDocumentAnalysisFlag) and
 * decide the evidence (UpdateDocumentAnalysisAssessmentStatus). The section's own route always
 * lands on indexAction, so leaving the change page and coming back shows the tabs again.
 */
abstract class AbstractFinancialEvidenceAssessmentController extends AbstractController implements
    ToggleAwareInterface
{
    /** Only completed, successful analyses are shown; pending and failed ones have nothing to assess. */
    private const string ANALYSIS_STATUS_SUCCESS = 'SUCCESS';

    /**
     * One tab per analysis, so a single page is fetched at the largest limit the transfer
     * validation allows. An LVA with more successful analyses than this shows only the newest.
     */
    private const int ANALYSIS_PAGE_LIMIT = 100;

    /** Posted as the value of the review form's button, so the review action is explicit. */
    public const string REVIEW_ACCEPT = 'accept';

    /** Posted as the values of the change page's buttons, so each form's action is explicit. */
    public const string CHANGE_OVERRIDE = 'override';
    public const string CHANGE_DECIDE = 'decide';

    /** The action route segment for the change document review page. */
    public const string ACTION_CHANGE_REVIEW = 'change-review';

    /**
     * The key the API gives the refusal to approve while issues remain unchanged
     * (UpdateDocumentAnalysisAssessmentStatus::ERR_UNCHANGED_ISSUES in the API).
     */
    public const string ERR_UNCHANGED_ISSUES = 'ERR_DOCUMENT_ANALYSIS_UNCHANGED_ISSUES';

    private const string SECTION_ROUTE = 'lva-%s/financial_evidence_assessment';

    protected string $location = 'internal';

    protected $toggleConfig = [
        'default' => [FeatureToggle::IDP],
    ];

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected StringHelperService $stringHelper,
        protected RestrictionHelperService $restrictionHelper,
        protected FlashMessengerHelperService $flashMessengerHelper,
        // Builds the review form; the licence context also uses it for its header search form.
        protected FormHelperService $formHelper,
        protected TableFactory $tableFactory,
        protected $navigation
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    #[\Override]
    public function indexAction()
    {
        $reviewForm = $this->getReviewForm();

        if ($this->getRequest()->isPost()) {
            return $this->processReview($reviewForm);
        }

        $analyses = $this->getSuccessfulAnalyses();

        $view = new ViewModel([
            'title'        => 'lva.section.title.financial_evidence_assessment',
            'hasDocuments' => $analyses !== [],
            'tabs'         => $this->getTabsFromAnalyses($analyses),
            'reviewForm'   => $reviewForm,
        ]);
        $view->setTemplate('sections/lva/financial-evidence-assessment');

        return $this->render($view);
    }

    protected function getReviewForm(): FormInterface
    {
        // No "continue" button: the form carries its own review action.
        return $this->formHelper->createForm(FinancialEvidenceAssessmentReview::class, true, false);
    }

    /**
     * Send the accepted review to the API and report its outcome, then redirect back to the page
     * (post/redirect/get) so a refresh cannot resubmit it.
     *
     * The posted analysis must be one this page lists for its application or licence. That is a
     * guard against a stale or edited form, not authorisation: the API decides the outcome from
     * its own stored result and accepts only successful analyses.
     */
    protected function processReview(FormInterface $reviewForm)
    {
        $post = $this->getRequest()->getPost();
        $reviewForm->setData($post);

        if ($post->get('review') !== self::REVIEW_ACCEPT || !$reviewForm->isValid()) {
            $this->flashMessengerHelper->addUnknownError();

            return $this->redirect()->refresh();
        }

        $analysisId = (int)$reviewForm->getData()['analysisId'];
        $analysis = $this->findSuccessfulAnalysis($analysisId);

        if ($analysis === null) {
            $this->flashMessengerHelper->addUnknownError();

            return $this->redirect()->refresh();
        }

        if (!FinancialEvidenceAssessmentTab::mapFromAnalysis($analysis)['hasAssessment']) {
            $this->flashMessengerHelper->addErrorMessage('This document has no assessment to review');

            return $this->redirect()->refresh();
        }

        $response = $this->handleCommand(AcceptDocumentAnalysisReview::create(['id' => $analysisId]));

        // The API reports what it decided in the result's flags; anything else reads as not recorded.
        $outcome = $response->isOk()
            ? AssessmentStatus::tryFrom((string)($response->getResult()['flags']['assessmentStatus'] ?? ''))
            : null;

        if ($outcome === AssessmentStatus::APPROVED) {
            $this->flashMessengerHelper->addSuccessMessage('Document review accepted: the document is approved');
        } elseif ($outcome === AssessmentStatus::REJECTED) {
            $this->flashMessengerHelper->addWarningMessage(
                'Document review accepted: the document is rejected because one or more checks did not pass'
            );
        } else {
            $this->flashMessengerHelper->addErrorMessage('The document review could not be recorded');
        }

        return $this->redirect()->refresh();
    }

    /**
     * One of the analyses this page shows, or null if the id is not among them. There is no query
     * for a single analysis, so this reuses the page's own, scoped to its application or licence.
     */
    protected function findSuccessfulAnalysis(int $analysisId): ?array
    {
        foreach ($this->getSuccessfulAnalyses() as $analysis) {
            if ((int)($analysis['id'] ?? 0) === $analysisId) {
                return $analysis;
            }
        }

        return null;
    }

    /**
     * Successful analyses for the current LVA context, most recently completed first.
     *
     * getIdentifierIndex() is 'licence' on licence pages and 'application' on application and
     * variation pages (a variation is an application in the API), so the one query covers all three.
     */
    protected function getSuccessfulAnalyses(): array
    {
        $response = $this->handleQuery(
            DocumentAnalysisList::create([
                $this->getIdentifierIndex() => $this->getIdentifier(),
                'status' => self::ANALYSIS_STATUS_SUCCESS,
                // The query is paged and ordered, so these are required. "Latest" means the most
                // recently completed successful analysis, so the API orders by completion.
                'page' => 1,
                'limit' => self::ANALYSIS_PAGE_LIMIT,
                'sort' => 'completedAt',
                'order' => 'DESC',
            ])
        );

        if (!$response->isOk()) {
            return [];
        }

        return $response->getResult()['analyses'] ?? [];
    }

    /**
     * One tab per successful analysis; the first (most recent) is labelled "Latest". The tab
     * header (id, label, date, caseworker review) is built here; the panel content (document link,
     * summary rows, issue count) comes from the mapper.
     */
    protected function getTabsFromAnalyses(array $analyses): array
    {
        $tabs = [];

        foreach ($analyses as $analysis) {
            $date = isset($analysis['documentDate'])
                ? (new \DateTime($analysis['documentDate']))->format('d/m/Y')
                : null;
            $isLatest = $tabs === [];

            // Null when the analysis has not been reviewed (or holds a value this app does not know).
            $assessmentStatus = AssessmentStatus::tryFrom((string)($analysis['assessmentStatus'] ?? ''));

            $tab = [
                'id'         => $isLatest ? 'latest' : 'analysis-' . $analysis['id'],
                'analysisId' => $analysis['id'],
                'label'      => $isLatest ? 'Latest' : ($date ?? 'Unknown date'),
                'date'       => $date,
                'status'     => $this->mapStatus($assessmentStatus),
                'statusTag'  => $this->mapStatusTagClass($assessmentStatus),
            ] + FinancialEvidenceAssessmentTab::mapFromAnalysis($analysis);

            // A decided review cannot change (its flags cannot), and without an assessment there is
            // nothing to decide on.
            $tab['canReview'] = $tab['hasAssessment']
                && !in_array($assessmentStatus, [AssessmentStatus::APPROVED, AssessmentStatus::REJECTED], true);

            $tab['changeReviewUrl'] = $tab['hasAssessment'] ? $this->changeReviewUrl((int)$analysis['id']) : null;

            $tabs[] = $tab;
        }

        return $tabs;
    }

    /**
     * The change document review page for one analysis: each failed or skipped check with a form
     * to change it to a pass, the decision on the evidence, and the checks that passed.
     *
     * Only an analysis this page lists, with an assessment and not yet decided, can be changed;
     * anything else goes back to the tabs. Both forms post back here and redirect (post/redirect/get).
     */
    public function changeReviewAction()
    {
        $analysisId = (int)$this->params('child_id');
        $analysis = $this->findSuccessfulAnalysis($analysisId);

        if ($analysis === null) {
            $this->flashMessengerHelper->addUnknownError();

            return $this->redirectToIndex();
        }

        $page = ReviewPageMapper::mapFromAnalysis($analysis);

        if (!$page['hasAssessment']) {
            $this->flashMessengerHelper->addErrorMessage('This document has no assessment to review');

            return $this->redirectToIndex();
        }

        $overrideForm = $this->formHelper->createForm(FinancialEvidenceIssueOverride::class, true, false);
        $decisionForm = $this->formHelper->createForm(FinancialEvidenceDecision::class, true, false);

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            if ($post->get('saveOverride') === self::CHANGE_OVERRIDE) {
                return $this->processOverride($overrideForm, $analysisId, $page);
            }

            if ($post->get('saveDecision') === self::CHANGE_DECIDE) {
                return $this->processDecision($decisionForm, $analysisId);
            }

            $this->flashMessengerHelper->addUnknownError();

            return $this->redirect()->refresh();
        }

        $decisionForm->get('analysisId')->setValue((string)$analysisId);

        $issues = array_map(
            fn(array $issue): array => $issue + [
                'table' => $this->tableFactory->prepareTable('financial-evidence-assessment-issue', $issue['tableRows']),
            ],
            $page['issues']
        );

        $view = new ViewModel([
            'title'        => 'lva.section.title.financial_evidence_assessment',
            'analysisId'   => $analysisId,
            'document'     => $page['document'],
            'issues'       => $issues,
            'passCount'    => $page['passCount'],
            'passesTable'  => $this->tableFactory->prepareTable('financial-evidence-assessment-passes', $page['passes']),
            'overrideForm' => $overrideForm,
            'decisionForm' => $decisionForm,
            'backUrl'      => $this->url()->fromRoute($this->sectionRoute(), [], [], true),
        ]);
        $view->setTemplate('sections/lva/financial-evidence-assessment-change-review');

        return $this->render($view);
    }

    /**
     * Change one issue to a pass. "No" changes nothing; "Yes" needs a reason, and the API refuses
     * a row that is not currently a fail or skipped, or an analysis already decided.
     */
    protected function processOverride(FormInterface $form, int $analysisId, array $page)
    {
        $post = $this->getRequest()->getPost();
        $form->setData($post);

        if (!$form->isValid()) {
            if ($post->get('changeRecommendation') === 'Y') {
                $this->flashMessengerHelper->addErrorMessage('Enter a reason for changing the recommendation');
            } else {
                $this->flashMessengerHelper->addUnknownError();
            }

            return $this->redirect()->refresh();
        }

        $data = $form->getData();

        if ((int)$data['analysisId'] !== $analysisId) {
            $this->flashMessengerHelper->addUnknownError();

            return $this->redirect()->refresh();
        }

        if ($data['changeRecommendation'] !== 'Y') {
            return $this->redirect()->refresh();
        }

        $changeable = array_column(
            array_filter($page['issues'], static fn(array $issue): bool => !$issue['changed']),
            'key'
        );

        if (!in_array($data['row'], $changeable, true)) {
            $this->flashMessengerHelper->addErrorMessage('This recommendation has already been changed');

            return $this->redirect()->refresh();
        }

        $response = $this->handleCommand(
            OverrideDocumentAnalysisFlag::create([
                'id' => $analysisId,
                'row' => $data['row'],
                'comment' => $data['comment'],
            ])
        );

        if ($response->isOk()) {
            $this->flashMessengerHelper->addSuccessMessage('The recommendation has been changed to a pass');
        } else {
            $this->flashMessengerHelper->addErrorMessage('The change to the recommendation could not be recorded');
        }

        return $this->redirect()->refresh();
    }

    /**
     * Record the caseworker's decision. The API refuses acceptance while any issue is unchanged;
     * that refusal is explained, anything else reads as not recorded. A recorded decision ends the
     * review, so the caseworker goes back to the tabs.
     */
    protected function processDecision(FormInterface $form, int $analysisId)
    {
        $form->setData($this->getRequest()->getPost());

        if (!$form->isValid()) {
            $this->flashMessengerHelper->addErrorMessage('Select a decision');

            return $this->redirect()->refresh();
        }

        $data = $form->getData();

        if ((int)$data['analysisId'] !== $analysisId) {
            $this->flashMessengerHelper->addUnknownError();

            return $this->redirect()->refresh();
        }

        $decision = AssessmentStatus::from((string)$data['decision']);

        $response = $this->handleCommand(
            UpdateDocumentAnalysisAssessmentStatus::create(['id' => $analysisId, 'status' => $decision->value])
        );

        if (!$response->isOk()) {
            $messages = $response->getResult()['messages'] ?? [];

            if (is_array($messages) && isset($messages[self::ERR_UNCHANGED_ISSUES])) {
                $this->flashMessengerHelper->addErrorMessage(
                    'You cannot change a fail to a pass while issues remain unchanged'
                );
            } else {
                $this->flashMessengerHelper->addErrorMessage('The decision could not be recorded');
            }

            return $this->redirect()->refresh();
        }

        $this->flashMessengerHelper->addSuccessMessage(
            $decision === AssessmentStatus::APPROVED
                ? 'Financial evidence accepted'
                : 'Financial evidence rejected'
        );

        return $this->redirectToIndex();
    }

    protected function sectionRoute(): string
    {
        return sprintf(self::SECTION_ROUTE, $this->lva);
    }

    protected function changeReviewUrl(int $analysisId): string
    {
        return $this->url()->fromRoute(
            $this->sectionRoute() . '/action',
            ['action' => self::ACTION_CHANGE_REVIEW, 'child_id' => $analysisId],
            [],
            true
        );
    }

    protected function redirectToIndex()
    {
        return $this->redirect()->toRoute($this->sectionRoute(), [], [], true);
    }

    protected function mapStatus(?AssessmentStatus $assessmentStatus): string
    {
        return match ($assessmentStatus) {
            AssessmentStatus::APPROVED => 'Approved',
            AssessmentStatus::REJECTED => 'Rejected',
            AssessmentStatus::PENDING  => 'Pending',
            null                       => 'Unknown',
        };
    }

    protected function mapStatusTagClass(?AssessmentStatus $assessmentStatus): string
    {
        return match ($assessmentStatus) {
            AssessmentStatus::APPROVED      => 'govuk-tag--green',
            AssessmentStatus::REJECTED      => 'govuk-tag--red',
            AssessmentStatus::PENDING, null => 'govuk-tag--grey',
        };
    }
}
