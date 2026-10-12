<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva;

use Common\Service\Cqrs\Response as CqrsResponse;
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
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\Session\Container;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\AbstractFinancialEvidenceAssessmentController;
use Olcs\Controller\Lva\Application\FinancialEvidenceAssessmentController as ApplicationController;
use Olcs\Controller\Lva\Factory\Controller\FinancialEvidenceAssessmentControllerFactory;
use Olcs\Controller\Lva\Licence\FinancialEvidenceAssessmentController as LicenceController;
use Olcs\Controller\Lva\Variation\FinancialEvidenceAssessmentController as VariationController;
use Olcs\Data\Mapper\FinancialEvidenceAssessmentReview as ReviewPageMapper;
use Olcs\Form\Model\Form\Lva\FinancialEvidenceAssessmentReview;
use Olcs\Form\Model\Form\Lva\FinancialEvidenceDecision;
use Olcs\Form\Model\Form\Lva\FinancialEvidenceIssueOverride;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;

/**
 * Behaviour shared by the licence, application and variation assessment pages.
 */
class FinancialEvidenceAssessmentControllerTest extends MockeryTestCase
{
    /**
     * A variation is an application in the API, so only the licence page scopes by licence.
     */
    public static function contextProvider(): \Iterator
    {
        yield 'licence' => [LicenceController::class, 'licence'];
        yield 'application' => [ApplicationController::class, 'application'];
        yield 'variation' => [VariationController::class, 'application'];
    }

    #[DataProvider('contextProvider')]
    public function testAnalysesAreScopedToTheLvaContextAndSuccessOnly(string $class, string $expectedKey): void
    {
        $sut = $this->createSut($class);
        $sut->expects('getIdentifier')->andReturn(42);

        $sut->expects('handleQuery')
            ->with(m::on(static function ($query) use ($expectedKey): bool {
                if (!$query instanceof DocumentAnalysisList) {
                    return false;
                }

                $otherKey = $expectedKey === 'licence' ? 'application' : 'licence';

                // Asserting the other scope is absent stops a query leaking across contexts.
                // The paging and ordering fields are required by the transfer validation, and
                // the API's completion ordering is what makes the first row "Latest".
                return (int) $query->{'get' . ucfirst($expectedKey)}() === 42
                    && $query->{'get' . ucfirst($otherKey)}() === null
                    && $query->getStatus() === 'SUCCESS'
                    && $query->getPage() === 1
                    && $query->getLimit() === 100
                    && $query->getSort() === 'completedAt'
                    && $query->getOrder() === 'DESC';
            }))
            ->andReturn($this->okResponse([]));

        $sut->expects('render')->andReturnUsing(static fn(ViewModel $view) => $view);

        $view = $sut->indexAction();

        $this->assertSame([], $view->getVariable('tabs'));
        $this->assertFalse($view->getVariable('hasDocuments'));
    }

    /**
     * Tabs come solely from successful analyses, in the order the API returns them (newest
     * completion first, as the query asks), with the date carried on each analysis. No document
     * list is queried.
     */
    public function testTabsAreBuiltFromAnalysesOnly(): void
    {
        $sut = $this->createSut(LicenceController::class);
        $sut->allows('getIdentifier')->andReturn(42);

        $sut->expects('handleQuery')
            ->once()
            ->andReturn($this->okResponse([
                ['id' => 2, 'documentId' => 12, 'documentDate' => '2026-02-02 00:00:00', 'completedAt' => '2026-02-03 00:00:00'],
                ['id' => 1, 'documentId' => 11, 'documentDate' => '2026-01-02 00:00:00', 'completedAt' => '2026-01-03 00:00:00'],
                ['id' => 3, 'documentId' => 13, 'documentDate' => null, 'completedAt' => '2025-12-01 00:00:00'],
            ]));

        $sut->expects('render')->andReturnUsing(static fn(ViewModel $view) => $view);

        $view = $sut->indexAction();
        $tabs = $view->getVariable('tabs');

        $this->assertTrue($view->getVariable('hasDocuments'));
        $this->assertSame(['latest', 'analysis-1', 'analysis-3'], array_column($tabs, 'id'));
        $this->assertSame(['Latest', '02/01/2026', 'Unknown date'], array_column($tabs, 'label'));
        $this->assertSame('02/02/2026', $tabs[0]['date']);
    }

    /**
     * Each tab carries its panel content from the mapper: the document link, the summary rows
     * built from the API's normalised result, and the issue count. The mapper has its own tests;
     * this proves the controller feeds it the analysis and keeps what it returns.
     */
    public function testTabsCarryTheMappedAssessmentContent(): void
    {
        $sut = $this->createSut(ApplicationController::class);
        $sut->allows('getIdentifier')->andReturn(42);

        $sut->expects('handleQuery')
            ->once()
            ->andReturn($this->okResponse([
                [
                    'id' => 2,
                    'documentId' => 12,
                    'documentDescription' => 'August statement',
                    'documentFilename' => null,
                    'documentDate' => '2026-02-02 00:00:00',
                    'completedAt' => '2026-02-03 00:00:00',
                    'resultNormalised' => [
                        'version' => 1,
                        'rows' => [
                            'bank' => ['flag' => null, 'remark' => null, 'value' => 'Example Bank', 'checks' => []],
                            'name' => ['flag' => 'fail', 'remark' => 'Wrong entity.', 'value' => 'Someone', 'checks' => []],
                        ],
                    ],
                ],
                // A stored report the API could not normalise still gets a tab.
                ['id' => 1, 'documentId' => 11, 'documentDate' => null, 'completedAt' => '2026-01-03 00:00:00', 'resultNormalised' => null],
            ]));

        $sut->expects('render')->andReturnUsing(static fn(ViewModel $view) => $view);

        $tabs = $sut->indexAction()->getVariable('tabs');

        $this->assertSame(['id' => 12, 'name' => 'August statement'], $tabs[0]['document']);
        $this->assertTrue($tabs[0]['hasAssessment']);
        $this->assertCount(8, $tabs[0]['rows']);
        $this->assertSame('Example Bank', $tabs[0]['rows'][0]['value']);
        $this->assertSame('FAIL', $tabs[0]['rows'][3]['flag']);
        $this->assertSame(1, $tabs[0]['issueCount']);

        $this->assertFalse($tabs[1]['hasAssessment']);
        $this->assertSame([], $tabs[1]['rows']);
    }

    /** A failed API call shows no tabs rather than erroring the page. */
    public function testFailedAnalysisQueryShowsNoTabs(): void
    {
        $sut = $this->createSut(ApplicationController::class);
        $sut->allows('getIdentifier')->andReturn(42);

        $response = m::mock(CqrsResponse::class);
        $response->allows('isOk')->andReturnFalse();
        $sut->expects('handleQuery')->andReturn($response);
        $sut->expects('render')->andReturnUsing(static fn(ViewModel $view) => $view);

        $this->assertSame([], $sut->indexAction()->getVariable('tabs'));
    }

    /** The view renders the review form built by the form helper, CSRF element included. */
    public function testViewCarriesTheReviewForm(): void
    {
        $reviewForm = m::mock(Form::class);

        $sut = $this->createSut(ApplicationController::class, null, $reviewForm);
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->expects('handleQuery')->andReturn($this->okResponse([]));
        $sut->expects('render')->andReturnUsing(static fn(ViewModel $view) => $view);

        $this->assertSame($reviewForm, $sut->indexAction()->getVariable('reviewForm'));
    }

    public static function assessmentStatusProvider(): \Iterator
    {
        // A decided review cannot be accepted again; anything undecided still can.
        yield 'approved' => ['APPROVED', 'Approved', 'govuk-tag--green', false];
        yield 'rejected' => ['REJECTED', 'Rejected', 'govuk-tag--red', false];
        yield 'pending' => ['PENDING', 'Pending', 'govuk-tag--grey', true];
        yield 'not reviewed' => [null, 'Unknown', 'govuk-tag--grey', true];
        yield 'unrecognised' => ['SOMETHING_ELSE', 'Unknown', 'govuk-tag--grey', true];
    }

    #[DataProvider('assessmentStatusProvider')]
    public function testTabsCarryTheCaseworkerReview(
        ?string $assessmentStatus,
        string $expectedStatus,
        string $expectedTag,
        bool $expectedCanReview
    ): void {
        $sut = $this->createSut(ApplicationController::class);
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->expects('handleQuery')->andReturn($this->okResponse([
            $this->assessedAnalysis(9, 'pass', $assessmentStatus),
        ]));
        $sut->expects('render')->andReturnUsing(static fn(ViewModel $view) => $view);

        $tab = $sut->indexAction()->getVariable('tabs')[0];

        $this->assertSame(9, $tab['analysisId']);
        $this->assertSame($expectedStatus, $tab['status']);
        $this->assertSame($expectedTag, $tab['statusTag']);
        $this->assertSame($expectedCanReview, $tab['canReview']);
    }

    /** Without an assessment there is nothing to decide on, so the review is not offered. */
    public function testTabWithoutAnAssessmentCannotBeReviewed(): void
    {
        $sut = $this->createSut(ApplicationController::class);
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->expects('handleQuery')->andReturn($this->okResponse([
            ['id' => 9, 'documentId' => 12, 'documentDate' => null, 'resultNormalised' => null],
        ]));
        $sut->expects('render')->andReturnUsing(static fn(ViewModel $view) => $view);

        $this->assertFalse($sut->indexAction()->getVariable('tabs')[0]['canReview']);
    }

    /**
     * Accepting sends the API the analysis id and nothing else: no status, no context, nothing
     * posted beyond the id. The API decides the outcome and the page reports what it decided.
     */
    public static function contextClassProvider(): \Iterator
    {
        foreach (self::contextProvider() as $name => [$class]) {
            yield $name => [$class];
        }
    }

    #[DataProvider('contextClassProvider')]
    public function testAcceptSendsOnlyTheAnalysisIdAndReportsTheApiOutcome(string $class): void
    {
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $flashMessenger->expects('addSuccessMessage')->with('Document review accepted: the document is approved');

        $post = [
            'analysisId' => '9',
            'review' => AbstractFinancialEvidenceAssessmentController::REVIEW_ACCEPT,
            'application' => '666',
            'licence' => '666',
            'status' => 'REJECTED',
        ];

        $sut = $this->createSut($class, $flashMessenger, $this->reviewForm($post, true));
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->expects('handleQuery')->andReturn($this->okResponse([
            $this->assessedAnalysis(8, 'pass'),
            // The page's own view of the flags plays no part in the outcome.
            $this->assessedAnalysis(9, 'fail'),
        ]));
        $sut->expects('handleCommand')
            ->with(m::on(static fn($command): bool => $command instanceof AcceptDocumentAnalysisReview
                && $command->getArrayCopy() === ['id' => 9]))
            ->andReturn($this->commandResponse(true, 'APPROVED'));
        $response = $this->expectRedirectToRefresh($sut);

        $this->assertSame($response, $sut->indexAction());
    }

    public static function outcomeProvider(): \Iterator
    {
        yield 'approved' => [
            true,
            'APPROVED',
            'addSuccessMessage',
            'Document review accepted: the document is approved',
        ];
        yield 'rejected' => [
            true,
            'REJECTED',
            'addWarningMessage',
            'Document review accepted: the document is rejected because one or more checks did not pass',
        ];
        yield 'api refused it' => [false, null, 'addErrorMessage', 'The document review could not be recorded'];
        yield 'api reported no outcome' => [true, null, 'addErrorMessage', 'The document review could not be recorded'];
        yield 'api reported an outcome this app does not know' => [
            true,
            'SOMETHING_ELSE',
            'addErrorMessage',
            'The document review could not be recorded',
        ];
    }

    #[DataProvider('outcomeProvider')]
    public function testAcceptReportsTheOutcome(bool $ok, ?string $status, string $flashMethod, string $message): void
    {
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $flashMessenger->expects($flashMethod)->with($message);

        $post = ['analysisId' => '9', 'review' => AbstractFinancialEvidenceAssessmentController::REVIEW_ACCEPT];

        $sut = $this->createSut(ApplicationController::class, $flashMessenger, $this->reviewForm($post, true));
        $sut->expects('handleCommand')->andReturn($this->commandResponse($ok, $status));
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->expects('handleQuery')->andReturn($this->okResponse([$this->assessedAnalysis(9, 'pass')]));
        $response = $this->expectRedirectToRefresh($sut);

        $this->assertSame($response, $sut->indexAction());
    }

    /** An analysis that is not on this page (another application's, or not successful) is not sent. */
    public function testAcceptForAnAnalysisNotOnThePageSendsNothing(): void
    {
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $flashMessenger->expects('addUnknownError');

        $post = ['analysisId' => '9', 'review' => AbstractFinancialEvidenceAssessmentController::REVIEW_ACCEPT];

        $sut = $this->createSut(ApplicationController::class, $flashMessenger, $this->reviewForm($post, true));
        $sut->shouldNotReceive('handleCommand');
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->expects('handleQuery')->andReturn($this->okResponse([$this->assessedAnalysis(8, 'pass')]));
        $response = $this->expectRedirectToRefresh($sut);

        $this->assertSame($response, $sut->indexAction());
    }

    public function testAcceptWithoutAnAssessmentSendsNothing(): void
    {
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $flashMessenger->expects('addErrorMessage')->with('This document has no assessment to review');

        $post = ['analysisId' => '9', 'review' => AbstractFinancialEvidenceAssessmentController::REVIEW_ACCEPT];

        $sut = $this->createSut(ApplicationController::class, $flashMessenger, $this->reviewForm($post, true));
        $sut->shouldNotReceive('handleCommand');
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->expects('handleQuery')->andReturn($this->okResponse([
            ['id' => 9, 'documentId' => 12, 'documentDate' => null, 'resultNormalised' => null],
        ]));
        $response = $this->expectRedirectToRefresh($sut);

        $this->assertSame($response, $sut->indexAction());
    }

    public static function rejectedPostProvider(): \Iterator
    {
        // The form is valid, but the post asks for no review action, or one not supported yet.
        yield 'no review action' => [['analysisId' => '9'], true];
        yield 'unknown review action' => [['analysisId' => '9', 'review' => 'reject'], true];
        // The form's validation (e.g. a missing or non-numeric analysis id) fails.
        yield 'invalid form' => [['analysisId' => '9 OR 1=1', 'review' => 'accept'], false];
    }

    /** A post the page did not build sends nothing. */
    #[DataProvider('rejectedPostProvider')]
    public function testRejectedPostSendsNothing(array $post, bool $formIsValid): void
    {
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $flashMessenger->expects('addUnknownError');

        $sut = $this->createSut(ApplicationController::class, $flashMessenger, $this->reviewForm($post, $formIsValid));
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->shouldNotReceive('handleQuery');
        $sut->shouldNotReceive('handleCommand');
        $response = $this->expectRedirectToRefresh($sut);

        $this->assertSame($response, $sut->indexAction());
    }

    /**
     * The API's refusal to approve says what to do next; the page shows its words, so the two
     * cannot drift apart. A refusal without usable text falls back to the same wording.
     */
    public static function approvalRefusalProvider(): \Iterator
    {
        yield 'api wording' => ['Wording from the API', 'Wording from the API'];
        yield 'no usable wording' => [['unexpected' => 'shape'], AbstractFinancialEvidenceAssessmentController::MSG_UNCHANGED_ISSUES];
    }

    #[DataProvider('approvalRefusalProvider')]
    public function testRefusedApprovalShowsWhatToDoNext(mixed $apiMessage, string $expected): void
    {
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $flashMessenger->expects('addErrorMessage')->with($expected);

        $response = $this->decide(
            $flashMessenger,
            $this->refusal([AbstractFinancialEvidenceAssessmentController::ERR_UNCHANGED_ISSUES => $apiMessage])
        );

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testOtherRefusalsReadAsNotRecorded(): void
    {
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $flashMessenger->expects('addErrorMessage')->with('The decision could not be recorded');

        $this->decide($flashMessenger, $this->refusal(['SOMETHING_ELSE' => 'Not for the caseworker']));
    }

    public function testFallbackWordingTellsTheCaseworkerWhatToDo(): void
    {
        $this->assertSame(
            'Change all failed and skipped checks to a pass before you accept the financial evidence',
            AbstractFinancialEvidenceAssessmentController::MSG_UNCHANGED_ISSUES
        );
    }

    #[DataProvider('contextProvider')]
    public function testFactoryCreatesEachContext(string $class, string $scopeKey): void
    {
        $formHelper = m::mock(FormHelperService::class);
        $container = m::mock(ContainerInterface::class);
        $container->allows('get')->with(NiTextTranslation::class)->andReturn(m::mock(NiTextTranslation::class));
        $container->allows('get')->with(AuthorizationService::class)->andReturn(m::mock(AuthorizationService::class));
        $container->allows('get')->with(StringHelperService::class)->andReturn(new StringHelperService());
        $container->allows('get')->with(RestrictionHelperService::class)->andReturn(m::mock(RestrictionHelperService::class));
        $container->allows('get')->with(FlashMessengerHelperService::class)->andReturn(m::mock(FlashMessengerHelperService::class));
        $container->expects('get')->with(FormHelperService::class)->andReturn($formHelper);
        $container->allows('get')->with(TableFactory::class)->andReturn(m::mock(TableFactory::class));
        $container->allows('get')->with('navigation')->andReturn([]);

        $controller = (new FinancialEvidenceAssessmentControllerFactory())($container, $class);

        $this->assertInstanceOf($class, $controller);
        // Proves each concrete controller's $lva maps to the scope the API query will use.
        $this->assertSame(
            $scopeKey,
            (new \ReflectionMethod($controller, 'getIdentifierIndex'))->invoke($controller)
        );

        if ($controller instanceof LicenceController) {
            // Exercise the trait through the real factory so a missing dependency cannot hide behind a mock.
            $form = m::mock(Form::class);
            $formHelper->expects('createForm')->with('HeaderSearch', false, false)->andReturn($form);
            $form->expects('bind')->with(m::on(
                static fn($session): bool => $session instanceof Container && $session->getName() === 'search'
            ));

            $this->assertSame($form, $controller->getSearchForm());
            // Reusing the form preserves the header search query without rebinding the session.
            $this->assertSame($form, $controller->getSearchForm());
        }
    }

    public function testFactoryRejectsUnrelatedClasses(): void
    {
        $this->expectException(ServiceNotCreatedException::class);

        (new FinancialEvidenceAssessmentControllerFactory())(m::mock(ContainerInterface::class), \stdClass::class);
    }

    // ---- changeReviewAction ----

    public function testChangeReviewForAnAnalysisNotOnThePageGoesBackToTheTabs(): void
    {
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->expects('addUnknownError');

        $sut = $this->createSut(ApplicationController::class, $flash);
        $sut->allows('params')->with('child_id')->andReturn('9');
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->expects('handleQuery')->andReturn($this->okResponse([$this->assessedAnalysis(8, 'fail')]));
        $response = new Response();
        $sut->expects('redirectToIndex')->andReturn($response);

        $this->assertSame($response, $sut->changeReviewAction());
    }

    public function testChangeReviewWithoutAnAssessmentGoesBackToTheTabs(): void
    {
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->expects('addErrorMessage')->with('This document has no assessment to review');

        $sut = $this->createSut(ApplicationController::class, $flash);
        $sut->allows('params')->with('child_id')->andReturn('9');
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->expects('handleQuery')->andReturn($this->okResponse([
            ['id' => 9, 'documentId' => 12, 'documentDate' => null, 'resultNormalised' => null],
        ]));
        $response = new Response();
        $sut->expects('redirectToIndex')->andReturn($response);

        $this->assertSame($response, $sut->changeReviewAction());
    }

    public function testChangeReviewShowsIssuesPassesAndBothForms(): void
    {
        $analysisIdElement = m::mock(ElementInterface::class);
        $analysisIdElement->expects('setValue')->with('9');
        $decisionForm = m::mock(Form::class);
        $decisionForm->expects('get')->with('analysisId')->andReturn($analysisIdElement);
        $overrideForm = m::mock(Form::class);

        $formHelper = m::mock(FormHelperService::class);
        $formHelper->expects('createForm')->with(FinancialEvidenceIssueOverride::class, true, false)->andReturn($overrideForm);
        $formHelper->expects('createForm')->with(FinancialEvidenceDecision::class, true, false)->andReturn($decisionForm);

        $analysis = $this->assessedAnalysis(9, 'fail');
        $expectedPage = ReviewPageMapper::mapFromAnalysis($analysis);

        $tableFactory = m::mock(TableFactory::class);
        $tableFactory->expects('prepareTable')
            ->with('financial-evidence-assessment-passes', $expectedPage['passes'])
            ->andReturn('rendered-table');

        $url = m::mock(Url::class);
        $url->expects('fromRoute')
            ->with('lva-application/financial_evidence_assessment', [], [], true)
            ->andReturn('/back');

        $sut = $this->createSut(ApplicationController::class, null, null, $formHelper, $tableFactory);
        $sut->allows('params')->with('child_id')->andReturn('9');
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->allows('getRequest')->andReturn(new Request());
        $sut->expects('handleQuery')->andReturn($this->okResponse([$analysis]));
        $sut->expects('url')->andReturn($url);
        $sut->expects('render')->andReturnUsing(static fn(ViewModel $view) => $view);

        $view = $sut->changeReviewAction();

        $this->assertSame('sections/lva/financial-evidence-assessment-change-review', $view->getTemplate());
        $this->assertSame(9, $view->getVariable('analysisId'));
        $this->assertSame($expectedPage['issues'], $view->getVariable('issues'));
        $this->assertSame($expectedPage['passCount'], $view->getVariable('passCount'));
        $this->assertSame('rendered-table', $view->getVariable('passesTable'));
        $this->assertSame($overrideForm, $view->getVariable('overrideForm'));
        $this->assertSame($decisionForm, $view->getVariable('decisionForm'));
        $this->assertSame('/back', $view->getVariable('backUrl'));
    }

    /** A post that names neither of the page's two buttons sends nothing. */
    public function testChangeReviewPostWithNeitherButtonSendsNothing(): void
    {
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->expects('addUnknownError');

        $formHelper = m::mock(FormHelperService::class);
        $formHelper->allows('createForm')->andReturn(m::mock(Form::class));

        $sut = $this->createSut(ApplicationController::class, $flash, null, $formHelper);
        $sut->allows('params')->with('child_id')->andReturn('9');
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->allows('getRequest')->andReturn($this->postRequest(['analysisId' => '9']));
        $sut->expects('handleQuery')->andReturn($this->okResponse([$this->assessedAnalysis(9, 'fail')]));
        $sut->shouldNotReceive('handleCommand');
        $response = $this->expectRedirectToRefresh($sut);

        $this->assertSame($response, $sut->changeReviewAction());
    }

    // ---- processOverride ----

    public function testOverrideSendsTheRowAndReasonAndReportsSuccess(): void
    {
        $post = ['analysisId' => '9', 'row' => 'largeDeposit', 'changeRecommendation' => 'Y', 'comment' => 'Checked by hand'];
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->expects('addSuccessMessage')->with('The recommendation has been changed to a pass');

        $sut = $this->createSut(ApplicationController::class, $flash);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->expects('handleCommand')
            ->with(m::on(static fn($c): bool => $c instanceof OverrideDocumentAnalysisFlag
                && (int)$c->getId() === 9 && $c->getRow() === 'largeDeposit' && $c->getComment() === 'Checked by hand'))
            ->andReturn($this->commandResponse(true, null));
        $response = $this->expectRedirectToRefresh($sut);

        $this->assertSame($response, $this->override($sut, $post, true));
    }

    public function testOverrideRefusedByTheApiReadsAsNotRecorded(): void
    {
        $post = ['analysisId' => '9', 'row' => 'largeDeposit', 'changeRecommendation' => 'Y', 'comment' => 'Because'];
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->expects('addErrorMessage')->with('The change to the recommendation could not be recorded');

        $sut = $this->createSut(ApplicationController::class, $flash);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->expects('handleCommand')->andReturn($this->commandResponse(false, null));
        $this->expectRedirectToRefresh($sut);

        $this->override($sut, $post, true);
    }

    public function testOverrideChoosingNoChangesNothing(): void
    {
        $post = ['analysisId' => '9', 'row' => 'largeDeposit', 'changeRecommendation' => 'N', 'comment' => ''];

        $sut = $this->createSut(ApplicationController::class);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->shouldNotReceive('handleCommand');
        $this->expectRedirectToRefresh($sut);

        $this->override($sut, $post, true);
    }

    public function testOverrideWithoutAReasonAsksForOne(): void
    {
        $post = ['analysisId' => '9', 'row' => 'largeDeposit', 'changeRecommendation' => 'Y', 'comment' => ''];
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->expects('addErrorMessage')->with('Enter a reason for changing the recommendation');

        $sut = $this->createSut(ApplicationController::class, $flash);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->shouldNotReceive('handleCommand');
        $this->expectRedirectToRefresh($sut);

        $this->override($sut, $post, false);
    }

    public function testOverrideWithAnInvalidFormThatIsNotAMissingReasonReadsAsUnknownError(): void
    {
        $post = ['analysisId' => 'x', 'row' => 'largeDeposit', 'changeRecommendation' => 'N'];
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->expects('addUnknownError');

        $sut = $this->createSut(ApplicationController::class, $flash);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->shouldNotReceive('handleCommand');
        $this->expectRedirectToRefresh($sut);

        $this->override($sut, $post, false);
    }

    /** The posted analysis must be the one the page was opened for. */
    public function testOverrideForAnotherAnalysisSendsNothing(): void
    {
        $post = ['analysisId' => '10', 'row' => 'largeDeposit', 'changeRecommendation' => 'Y', 'comment' => 'x'];
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->expects('addUnknownError');

        $sut = $this->createSut(ApplicationController::class, $flash);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->shouldNotReceive('handleCommand');
        $this->expectRedirectToRefresh($sut);

        $this->override($sut, $post, true);
    }

    public static function unchangeableRowProvider(): \Iterator
    {
        yield 'already changed' => ['name'];
        yield 'not an issue' => ['bank'];
        yield 'unknown' => ['nonsense'];
    }

    /** A row that is not an unchanged issue on this page (already changed, a pass, or made up) is not sent. */
    #[DataProvider('unchangeableRowProvider')]
    public function testOverrideOfARowThatCannotBeChangedSendsNothing(string $row): void
    {
        $post = ['analysisId' => '9', 'row' => $row, 'changeRecommendation' => 'Y', 'comment' => 'x'];
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->expects('addErrorMessage')->with('This recommendation has already been changed');

        $analysis = $this->assessedAnalysis(9, 'fail');
        $analysis['resultNormalised']['rows']['name'] = [
            'flag' => 'pass', 'remark' => null, 'value' => null, 'checks' => [],
            'override' => ['originalFlag' => 'fail', 'comment' => 'x', 'changedBy' => 'Jo', 'changedOn' => null],
        ];

        $sut = $this->createSut(ApplicationController::class, $flash);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->shouldNotReceive('handleCommand');
        $this->expectRedirectToRefresh($sut);

        $this->override($sut, $post, true, $analysis);
    }

    // ---- processDecision (the refusals are covered above) ----

    public function testDecisionWithoutASelectionAsksForOne(): void
    {
        $post = ['analysisId' => '9', 'saveDecision' => 'decide'];
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->expects('addErrorMessage')->with('Select a decision');

        $sut = $this->createSut(ApplicationController::class, $flash);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->shouldNotReceive('handleCommand');
        $this->expectRedirectToRefresh($sut);

        (new \ReflectionMethod($sut, 'processDecision'))->invoke($sut, $this->reviewForm($post, false), 9);
    }

    public function testDecisionForAnotherAnalysisSendsNothing(): void
    {
        $post = ['analysisId' => '10', 'decision' => 'APPROVED', 'saveDecision' => 'decide'];
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->expects('addUnknownError');

        $sut = $this->createSut(ApplicationController::class, $flash);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->shouldNotReceive('handleCommand');
        $this->expectRedirectToRefresh($sut);

        (new \ReflectionMethod($sut, 'processDecision'))->invoke($sut, $this->reviewForm($post, true), 9);
    }

    public static function decisionProvider(): \Iterator
    {
        yield 'approved' => ['APPROVED', 'Financial evidence accepted'];
        yield 'rejected' => ['REJECTED', 'Financial evidence rejected'];
    }

    /** A recorded decision ends the review, so the caseworker goes back to the tabs. */
    #[DataProvider('decisionProvider')]
    public function testARecordedDecisionReportsItAndReturnsToTheTabs(string $decision, string $message): void
    {
        $post = ['analysisId' => '9', 'decision' => $decision, 'saveDecision' => 'decide'];
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->expects('addSuccessMessage')->with($message);

        $sut = $this->createSut(ApplicationController::class, $flash);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->expects('handleCommand')
            ->with(m::on(static fn($c): bool => $c instanceof UpdateDocumentAnalysisAssessmentStatus
                && (int)$c->getId() === 9 && $c->getStatus() === $decision))
            ->andReturn($this->commandResponse(true, $decision));
        $response = new Response();
        $sut->expects('redirectToIndex')->andReturn($response);

        $this->assertSame(
            $response,
            (new \ReflectionMethod($sut, 'processDecision'))->invoke($sut, $this->reviewForm($post, true), 9)
        );
    }

    // ---- status mapping and routes ----

    public static function statusProvider(): \Iterator
    {
        yield 'approved' => ['APPROVED', 'Approved', 'govuk-tag--green'];
        yield 'rejected' => ['REJECTED', 'Rejected', 'govuk-tag--red'];
        yield 'pending' => ['PENDING', 'Pending', 'govuk-tag--grey'];
        yield 'unknown' => [null, 'Unknown', 'govuk-tag--grey'];
    }

    #[DataProvider('statusProvider')]
    public function testStatusMapsToALabelAndATagColour(?string $status, string $label, string $tag): void
    {
        $sut = $this->createSut(ApplicationController::class);
        $enum = $status === null ? null : AssessmentStatus::from($status);

        $this->assertSame($label, (new \ReflectionMethod($sut, 'mapStatus'))->invoke($sut, $enum));
        $this->assertSame($tag, (new \ReflectionMethod($sut, 'mapStatusTagClass'))->invoke($sut, $enum));
    }

    public static function sectionRouteProvider(): \Iterator
    {
        yield 'licence' => [LicenceController::class, 'lva-licence/financial_evidence_assessment'];
        yield 'application' => [ApplicationController::class, 'lva-application/financial_evidence_assessment'];
        yield 'variation' => [VariationController::class, 'lva-variation/financial_evidence_assessment'];
    }

    #[DataProvider('sectionRouteProvider')]
    public function testSectionRouteFollowsTheLvaContext(string $class, string $route): void
    {
        $sut = $this->createSut($class);

        $this->assertSame($route, (new \ReflectionMethod($sut, 'sectionRoute'))->invoke($sut));
    }

    /** Runs processOverride for analysis 9 against $analysis (default: every flagged row failing). */
    private function override(m\MockInterface $sut, array $post, bool $formIsValid, ?array $analysis = null): mixed
    {
        $page = ReviewPageMapper::mapFromAnalysis($analysis ?? $this->assessedAnalysis(9, 'fail'));

        return (new \ReflectionMethod($sut, 'processOverride'))
            ->invoke($sut, $this->reviewForm($post, $formIsValid), 9, $page);
    }

    /**
     * @param class-string<AbstractFinancialEvidenceAssessmentController> $class
     */
    private function createSut(
        string $class,
        ?FlashMessengerHelperService $flashMessenger = null,
        ?Form $reviewForm = null,
        ?FormHelperService $formHelper = null,
        ?TableFactory $tableFactory = null
    ): AbstractFinancialEvidenceAssessmentController|m\MockInterface {
        // The review form comes from the form helper, which adds its CSRF element.
        if ($formHelper === null) {
            $formHelper = m::mock(FormHelperService::class);
            $formHelper->allows('createForm')
                ->with(FinancialEvidenceAssessmentReview::class, true, false)
                ->andReturn($reviewForm ?? m::mock(Form::class));
        }

        $sut = m::mock($class, [
            m::mock(NiTextTranslation::class),
            m::mock(AuthorizationService::class),
            new StringHelperService(),
            m::mock(RestrictionHelperService::class),
            $flashMessenger ?? m::mock(FlashMessengerHelperService::class),
            $formHelper,
            $tableFactory ?? m::mock(TableFactory::class),
            [],
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        // Building the link needs the router, which a unit test has none of; the route is config.
        $sut->allows('changeReviewUrl')->andReturnUsing(static fn(int $id): string => '/change-review/' . $id);

        return $sut;
    }

    /**
     * A successful analysis as DocumentAnalysisList returns it, with a current normalised result
     * whose six flagged rows all carry $flag (bank and bank address are never flagged).
     */
    private function assessedAnalysis(int $id, string $flag, ?string $assessmentStatus = null): array
    {
        $rows = [];

        foreach (['authenticity', 'name', 'statementDate', 'statementPeriod', 'averageFunds', 'largeDeposit'] as $key) {
            $rows[$key] = ['flag' => $flag, 'remark' => null, 'value' => null, 'checks' => []];
        }

        $rows['bank'] = ['flag' => null, 'remark' => null, 'value' => 'Example Bank', 'checks' => []];
        $rows['bankAddress'] = ['flag' => null, 'remark' => null, 'value' => '1 Example Street', 'checks' => []];

        return [
            'id' => $id,
            'documentId' => 100 + $id,
            'documentDate' => null,
            'assessmentStatus' => $assessmentStatus,
            'resultNormalised' => ['version' => 1, 'rows' => $rows],
        ];
    }

    /**
     * A review form that receives the post and reports the given validity.
     *
     * @param array<string, mixed> $post
     */
    private function reviewForm(array $post, bool $isValid): Form|m\MockInterface
    {
        $form = m::mock(Form::class);
        $form->expects('setData')->with(m::on(
            static fn($data): bool => $data instanceof Parameters && $data->toArray() === $post
        ));
        $form->allows('isValid')->andReturn($isValid);
        $form->allows('getData')->andReturn($post);

        return $form;
    }

    /**
     * @param array<string, mixed> $post
     */
    private function postRequest(array $post): Request
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost(new Parameters($post));

        return $request;
    }

    private function expectRedirectToRefresh(m\MockInterface $sut): Response
    {
        $response = new Response();
        $redirect = m::mock(Redirect::class);
        $redirect->expects('refresh')->andReturn($response);
        $sut->expects('redirect')->andReturn($redirect);

        return $response;
    }

    /** The API's response to AcceptDocumentAnalysisReview: the decided status travels in the result's flags. */
    private function commandResponse(bool $ok, ?string $assessmentStatus): CqrsResponse
    {
        $response = m::mock(CqrsResponse::class);
        $response->allows('isOk')->andReturn($ok);
        $response->allows('getResult')->andReturn([
            'id' => ['documentAnalysis' => 9],
            'messages' => [],
            'flags' => $assessmentStatus === null ? [] : ['assessmentStatus' => $assessmentStatus],
        ]);

        return $response;
    }

    private function okResponse(array $analyses): CqrsResponse
    {
        $response = m::mock(CqrsResponse::class);
        $response->allows('isOk')->andReturnTrue();
        $response->allows('getResult')->andReturn(['analyses' => $analyses]);

        return $response;
    }

    /**
     * Post an approval for analysis 9 through processDecision and return its redirect.
     */
    private function decide(FlashMessengerHelperService $flashMessenger, CqrsResponse $apiResponse): Response
    {
        $post = ['analysisId' => '9', 'decision' => 'APPROVED', 'saveDecision' => 'decide'];

        $sut = $this->createSut(ApplicationController::class, $flashMessenger);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->expects('handleCommand')
            ->with(m::on(static fn($command): bool => $command instanceof UpdateDocumentAnalysisAssessmentStatus
                && (int)$command->getId() === 9 && $command->getStatus() === 'APPROVED'))
            ->andReturn($apiResponse);
        $redirectResponse = $this->expectRedirectToRefresh($sut);

        $result = (new \ReflectionMethod($sut, 'processDecision'))->invoke($sut, $this->reviewForm($post, true), 9);
        $this->assertSame($redirectResponse, $result);

        return $result;
    }

    /** A refused command, with the API's messages keyed as it sends them. */
    private function refusal(array $messages): CqrsResponse
    {
        $response = m::mock(CqrsResponse::class);
        $response->allows('isOk')->andReturnFalse();
        $response->allows('getResult')->andReturn(['messages' => $messages]);

        return $response;
    }
}
