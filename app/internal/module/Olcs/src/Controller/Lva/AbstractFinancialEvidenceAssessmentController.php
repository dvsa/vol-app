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
use Dvsa\Olcs\Transfer\Command\Document\OverridePassedDocumentAnalysisFlag;
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
use Olcs\Form\Model\Form\Lva\FinancialEvidenceOverrideRules;
use Olcs\Form\Model\Form\Lva\FinancialEvidencePassOverride;

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
 * caseworker can change failed or skipped checks to passes (OverrideDocumentAnalysisFlag),
 * change passes back to fails (OverridePassedDocumentAnalysisFlag, shown via the ?view=passes
 * query flag rather than a route segment, since the LVA section route is shared by every
 * section and a new path segment there would change every section's URL shape), and decide
 * the evidence (UpdateDocumentAnalysisAssessmentStatus). Both directions are the same journey -
 * an answer on a check that can be revisited, changed back or cleared - so both are handled by
 * processOverride(), configured by OVERRIDE_JOURNEYS. The section's own route always
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
    public const string CHANGE_OVERRIDE_PASS = 'override-fail';
    public const string CHANGE_DECIDE = 'decide';

    /** The action route segment for the change document review page. */
    public const string ACTION_CHANGE_REVIEW = 'change-review';

    /**
     * The query flag that switches the change page to showing the passed checks, each with a
     * form to change it to a fail, instead of the read-only passes table.
     */
    public const string QUERY_VIEW = 'view';
    public const string VIEW_PASSES = 'passes';

    /**
     * The key the API gives the refusal to approve while issues remain unchanged
     * (UpdateDocumentAnalysisAssessmentStatus::ERR_UNCHANGED_ISSUES in the API).
     */
    public const string ERR_UNCHANGED_ISSUES = 'ERR_DOCUMENT_ANALYSIS_UNCHANGED_ISSUES';

    /**
     * The translation key shown for that refusal. The API sends its own English wording with it,
     * but text on this page comes from translation keys, so the refusal is recognised by its
     * error key and worded here. Says what to do next, not what is wrong.
     */
    public const string MSG_UNCHANGED_ISSUES = 'lva-financial-evidence-assessment-error-unchanged-issues';

    /**
     * The two ways a caseworker can answer a check, keyed by the value of the button that posts
     * them. Both are handled by processOverride(), which differs between them only by this:
     * the list of the page's checks the answer is about, the form's button, the command that
     * records it, and the flash messages for a change and for undoing one.
     */
    private const array OVERRIDE_JOURNEYS = [
        self::CHANGE_OVERRIDE => [
            'list' => 'issues',
            'button' => 'saveOverride',
            'command' => OverrideDocumentAnalysisFlag::class,
            'changed' => 'lva-financial-evidence-assessment-message-changed-to-pass',
            'changedBack' => 'lva-financial-evidence-assessment-message-changed-back-to-original',
        ],
        self::CHANGE_OVERRIDE_PASS => [
            'list' => 'passes',
            'button' => 'savePassOverride',
            'command' => OverridePassedDocumentAnalysisFlag::class,
            'changed' => 'lva-financial-evidence-assessment-message-changed-to-fail',
            'changedBack' => 'lva-financial-evidence-assessment-message-changed-back-to-pass',
        ],
    ];

    /** The decision form's only field the caseworker fills in; the rest are hidden. */
    private const array DECISION_CASEWORKER_FIELDS = ['decision'];

    private const string SECTION_ROUTE = 'lva-%s/financial_evidence_assessment';

    /**
     * The check (an issue or a pass) whose form is being shown again with errors, or null. The
     * errors and the values the caseworker entered are on the form object itself, as for any
     * other form.
     */
    protected ?string $overrideErrorRow = null;

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
            $this->flashMessengerHelper->addErrorMessage('lva-financial-evidence-assessment-message-no-assessment');

            return $this->redirect()->refresh();
        }

        $response = $this->handleCommand(AcceptDocumentAnalysisReview::create(['id' => $analysisId]));

        // The API reports what it decided in the result's flags; anything else reads as not recorded.
        $outcome = $response->isOk()
            ? AssessmentStatus::tryFrom((string)($response->getResult()['flags']['assessmentStatus'] ?? ''))
            : null;

        if ($outcome === AssessmentStatus::APPROVED) {
            $this->flashMessengerHelper->addSuccessMessage('lva-financial-evidence-assessment-message-accepted-approved');
        } elseif ($outcome === AssessmentStatus::REJECTED) {
            $this->flashMessengerHelper->addWarningMessage(
                'lva-financial-evidence-assessment-message-accepted-rejected'
            );
        } else {
            $this->flashMessengerHelper->addErrorMessage('lva-financial-evidence-assessment-message-review-not-recorded');
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
        // Temporary caseworker stamp until stamping is implemented; independent of processing status.
        $caseworkerStamp = 'APPROVED';
        $tabs = [];

        foreach ($analyses as $analysis) {
            $date = isset($analysis['documentDate'])
                ? (new \DateTime($analysis['documentDate']))->format('d/m/Y')
                : null;
            $isLatest = $tabs === [];
            $dateLabel = $date ?? 'lva-financial-evidence-assessment-tab-unknown-date';

            // Null when the analysis has not been reviewed (or holds a value this app does not know).
            $assessmentStatus = AssessmentStatus::tryFrom((string)($analysis['assessmentStatus'] ?? ''));

            $tab = [
                'id'              => $isLatest ? 'latest' : 'analysis-' . $analysis['id'],
                'analysisId'      => $analysis['id'],
                'label'           => $isLatest ? 'lva-financial-evidence-assessment-tab-latest' : $dateLabel,
                // Optional: only set when the tab should read differently once deselected.
                'unselectedLabel' => $isLatest ? $dateLabel : null,
                'date'            => $date,
                'status'          => $this->mapStatus($assessmentStatus),
                'statusTag'       => $this->mapStatusTagClass($assessmentStatus),
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
     * to change it to a pass, the decision on the evidence, and the checks that passed - or, with
     * ?view=passes, each passed check with a form to change it to a fail instead.
     *
     * Only an analysis this page lists, with an assessment and not yet decided, can be changed;
     * anything else goes back to the tabs. All forms post back here and redirect (post/redirect/get).
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
            $this->flashMessengerHelper->addErrorMessage('lva-financial-evidence-assessment-message-no-assessment');

            return $this->redirectToIndex();
        }

        $overrideForms = [
            self::CHANGE_OVERRIDE => $this->formHelper->createForm(FinancialEvidenceIssueOverride::class, true, false),
            self::CHANGE_OVERRIDE_PASS => $this->formHelper->createForm(FinancialEvidencePassOverride::class, true, false),
        ];
        $decisionForm = $this->formHelper->createForm(FinancialEvidenceDecision::class, true, false);

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $journey = self::postedOverrideJourney($post);

            if ($journey !== null) {
                $response = $this->processOverride($journey, $overrideForms[$journey], $analysisId, $page);
            } elseif ($post->get('saveDecision') === self::CHANGE_DECIDE) {
                $response = $this->processDecision($decisionForm, $analysisId);
            } else {
                $this->flashMessengerHelper->addUnknownError();

                $response = $this->redirectBackToChangeReview();
            }

            // Null means the caseworker has something to put right: the page is drawn again
            // (no redirect) with the error against the field and what they entered kept.
            if ($response !== null) {
                return $response;
            }
        }

        $decisionForm->get('analysisId')->setValue((string)$analysisId);

        // Issues and passes are short key/value lists rendered directly by the view; only the
        // read-only passes table (shown unless ?view=passes) shares columns with the table builder.
        $view = new ViewModel([
            'title'            => 'lva.section.title.financial_evidence_assessment',
            'analysisId'       => $analysisId,
            'document'         => $page['document'],
            'issues'           => $page['issues'],
            'passes'           => $page['passes'],
            'passCount'        => $page['passCount'],
            'passesTable'      => $this->tableFactory->prepareTable('financial-evidence-assessment-passes', $page['passes']),
            'showPassOverride' => $this->getRequest()->getQuery(self::QUERY_VIEW) === self::VIEW_PASSES,
            'overrideForm'     => $overrideForms[self::CHANGE_OVERRIDE],
            'passOverrideForm' => $overrideForms[self::CHANGE_OVERRIDE_PASS],
            'overrideErrorRow' => $this->overrideErrorRow,
            'decisionForm'     => $decisionForm,
            'backUrl'          => $this->url()->fromRoute($this->sectionRoute(), [], [], true),
        ]);
        $view->setTemplate('sections/lva/financial-evidence-assessment-change-review');

        return $this->render($view);
    }

    /** Which of OVERRIDE_JOURNEYS was posted, by the value of its form's button, or null. */
    private static function postedOverrideJourney(\Laminas\Stdlib\ParametersInterface $post): ?string
    {
        foreach (self::OVERRIDE_JOURNEYS as $journey => $config) {
            if ($post->get($config['button']) === $journey) {
                return $journey;
            }
        }

        return null;
    }

    /**
     * Record the caseworker's answer on one check: change it (an issue to a pass, or a pass to a
     * fail, per $journey), or leave a note on it without changing its flag.
     *
     * The answer stands and can be revisited, so a check already changed is handled like any
     * other. "Yes" changes it (or keeps it changed and replaces the comment). "No" means the
     * analyser's outcome stands, which changes a check already changed back: with text, the text
     * is kept as a note; with none, the caseworker's entry for the check is deleted. "No" with
     * nothing typed on a check nobody has answered is nothing to send.
     *
     * Every rule the caseworker can fall foul of (no answer, "Yes" with no reason on a check that
     * was not skipped, text over the limit) is the form's, with its message. An invalid form is
     * shown again with those messages against their fields; nothing is sent to the API, which
     * applies the same rules to any other caller.
     *
     * @return \Laminas\Http\Response|null null when the page is to be drawn again with the form's errors
     */
    protected function processOverride(string $journey, FormInterface $form, int $analysisId, array $page)
    {
        $config = self::OVERRIDE_JOURNEYS[$journey];
        $post = $this->getRequest()->getPost();
        $row = (string)$post->get('row');
        $check = self::findByKey($page[$config['list']], $row);

        // Only a flagged check on this page's own list can be answered (bank details are listed
        // with the passes for reference, but carry no flag). Anything else is a stale or edited form.
        if ($check === null || !$check['flagged']) {
            $this->flashMessengerHelper->addUnknownError();

            return $this->redirectBackToChangeReview();
        }

        // An unanswered radio is not posted at all, but the reason's rule needs the key to read.
        // The check's analyser flag is the page's, never the post's: it decides whether a reason
        // is needed, so it cannot be left to what was sent.
        $form->setData(array_merge(
            ['changeRecommendation' => null],
            $post->toArray(),
            [FinancialEvidenceOverrideRules::CONTEXT_ANALYSER_FLAG => $check['flag']]
        ));

        if (!$form->isValid()) {
            return $this->formErrors($form, FinancialEvidenceOverrideRules::CASEWORKER_FIELDS, $row);
        }

        $data = $form->getData();

        if ((int)$data['analysisId'] !== $analysisId) {
            $this->flashMessengerHelper->addUnknownError();

            return $this->redirectBackToChangeReview();
        }

        $comment = trim((string)$data['comment']);
        $change = $data['changeRecommendation'] === 'Y';
        $changeBack = !$change && $check['changed'];

        if (!$change && $comment === '' && !($check['changed'] || $check['kept'])) {
            // Nothing to change, nothing to remove and nothing to say.
            return $this->redirectBackToChangeReview();
        }

        $response = $this->handleCommand(
            $config['command']::create([
                'id' => $analysisId,
                'row' => $row,
                'comment' => $comment,
                'changeRecommendation' => $data['changeRecommendation'],
            ])
        );

        if ($response->isOk()) {
            $this->flashMessengerHelper->addSuccessMessage(
                match (true) {
                    $change => $config['changed'],
                    $changeBack => $config['changedBack'],
                    $comment === '' => 'lva-financial-evidence-assessment-message-comment-removed',
                    default => 'lva-financial-evidence-assessment-message-comment-saved',
                }
            );
        } else {
            $this->flashMessengerHelper->addErrorMessage('lva-financial-evidence-assessment-message-change-not-recorded');
        }

        return $this->redirectBackToChangeReview();
    }

    /**
     * A form on the change page did not validate. If that is something the caseworker can put
     * right (the messages are all on fields they fill in), the page is drawn again with the
     * form's own messages against those fields and what they entered kept: formRow() renders
     * them, as on any other form. Returns null so changeReviewAction() carries on to render.
     *
     * Anything else is not theirs to fix (a hidden field with a bad value, an expired security
     * token), so it reads as an unknown error.
     *
     * @param list<string> $caseworkerFields
     * @param string|null  $row the check the form is about, if it is an override form
     *
     * @return \Laminas\Http\Response|null
     */
    protected function formErrors(FormInterface $form, array $caseworkerFields, ?string $row = null)
    {
        $fieldsInError = array_keys($form->getMessages());

        if ($fieldsInError === [] || array_diff($fieldsInError, $caseworkerFields) !== []) {
            $this->flashMessengerHelper->addUnknownError();

            return $this->redirectBackToChangeReview();
        }

        $this->overrideErrorRow = $row;

        return null;
    }

    /**
     * Record the caseworker's decision. The API refuses acceptance while any issue is unchanged;
     * that refusal is explained, anything else reads as not recorded. A recorded decision ends the
     * review, so the caseworker goes back to the tabs. No decision selected is shown against the
     * field, as the override forms' errors are.
     *
     * @return \Laminas\Http\Response|null null when the page is to be drawn again with the form's errors
     */
    protected function processDecision(FormInterface $form, int $analysisId)
    {
        $form->setData($this->getRequest()->getPost());

        if (!$form->isValid()) {
            return $this->formErrors($form, self::DECISION_CASEWORKER_FIELDS);
        }

        $data = $form->getData();

        if ((int)$data['analysisId'] !== $analysisId) {
            $this->flashMessengerHelper->addUnknownError();

            return $this->redirectBackToChangeReview();
        }

        $decision = AssessmentStatus::from((string)$data['decision']);

        $response = $this->handleCommand(
            UpdateDocumentAnalysisAssessmentStatus::create(['id' => $analysisId, 'status' => $decision->value])
        );

        if (!$response->isOk()) {
            $messages = $response->getResult()['messages'] ?? [];
            $refusal = is_array($messages) ? ($messages[self::ERR_UNCHANGED_ISSUES] ?? null) : null;

            if ($refusal !== null) {
                $this->flashMessengerHelper->addErrorMessage(self::MSG_UNCHANGED_ISSUES);
            } else {
                $this->flashMessengerHelper->addErrorMessage('lva-financial-evidence-assessment-message-decision-not-recorded');
            }

            return $this->redirectBackToChangeReview();
        }

        $this->flashMessengerHelper->addSuccessMessage(
            $decision === AssessmentStatus::APPROVED
                ? 'lva-financial-evidence-assessment-message-evidence-accepted'
                : 'lva-financial-evidence-assessment-message-evidence-rejected'
        );

        return $this->redirectToIndex();
    }

    /**
     * Back to this page (post/redirect/get), staying on the passed-checks view if that is where
     * the caseworker was. redirect()->refresh() rebuilds the URL from the route match alone and
     * drops ?view=passes, which would otherwise silently switch a failed or noted submission back
     * to the default (fails and changes) view.
     */
    protected function redirectBackToChangeReview()
    {
        $query = $this->getRequest()->getQuery(self::QUERY_VIEW) === self::VIEW_PASSES
            ? [self::QUERY_VIEW => self::VIEW_PASSES]
            : [];

        return $this->redirect()->toRoute(null, [], ['query' => $query], true);
    }

    /** The entry of $list (one of the mapper's issues or passes) whose 'key' matches, or null. */
    private static function findByKey(array $list, string $key): ?array
    {
        foreach ($list as $item) {
            if (($item['key'] ?? null) === $key) {
                return $item;
            }
        }

        return null;
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
            AssessmentStatus::APPROVED => 'lva-financial-evidence-assessment-status-approved',
            AssessmentStatus::REJECTED => 'lva-financial-evidence-assessment-status-rejected',
            AssessmentStatus::PENDING  => 'lva-financial-evidence-assessment-status-pending',
            null                       => 'lva-financial-evidence-assessment-status-unknown',
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
