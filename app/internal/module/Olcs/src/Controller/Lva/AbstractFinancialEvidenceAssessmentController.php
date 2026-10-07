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
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;
use Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\FormInterface;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Data\Mapper\FinancialEvidenceAssessmentTab;
use Olcs\Form\Model\Form\Lva\FinancialEvidenceAssessmentReview;
use Olcs\Service\FinancialEvidence\FinancialEvidenceAssessmentService;

/**
 * Financial evidence assessment page, shared by the licence, application and variation sections.
 *
 * Tabs are driven solely by successful document analyses. The concrete controllers only supply
 * the LVA context (via their trait and $lva), which decides whether analyses are scoped by
 * licence or by application. What each tab shows is decided by FinancialEvidenceAssessmentTab.
 *
 * Each tab can be reviewed by the caseworker through the FinancialEvidenceAssessmentReview form,
 * which posts back to this page. Its CSRF element is added by the form helper like any other form.
 * Accepting the review hands the analysis' flags to FinancialEvidenceAssessmentService, which
 * decides and records the outcome.
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
        protected $navigation,
        protected FinancialEvidenceAssessmentService $assessmentService
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
     * Assess one analysis from its flags and record the outcome, then redirect back to the page
     * (post/redirect/get) so a refresh cannot resubmit it.
     *
     * The analysis and its flags are re-read from the API for this page's application or licence,
     * never taken from the form: a posted flag could be edited in the browser. That also means only
     * an analysis shown on this page can be assessed.
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

        $flags = FinancialEvidenceAssessmentTab::flagsFromAnalysis($analysis);

        if ($flags === null) {
            $this->flashMessengerHelper->addErrorMessage('This document has no assessment to review');

            return $this->redirect()->refresh();
        }

        $outcome = $this->assessmentService->assess(
            $analysisId,
            $flags,
            $this->getIdentifierIndex(),
            (int)$this->getIdentifier()
        );

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

            $tabs[] = $tab;
        }

        return $tabs;
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
