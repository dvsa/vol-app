<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva;

use Common\Service\Cqrs\Response as CqrsResponse;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;
use Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Redirect;
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
use Olcs\Form\Model\Form\Lva\FinancialEvidenceAssessmentReview;
use Olcs\Service\FinancialEvidence\FinancialEvidenceAssessmentService;
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
     * Accepting hands the analysis' six flags, re-read from the API, to the assessment service with
     * the context from the route. Nothing posted for the context or the flags is used.
     */
    #[DataProvider('contextProvider')]
    public function testAcceptAssessesTheStoredFlagsScopedToTheLvaContext(string $class, string $expectedKey): void
    {
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $flashMessenger->expects('addSuccessMessage')->with('Document review accepted: the document is approved');

        $post = [
            'analysisId' => '9',
            'review' => AbstractFinancialEvidenceAssessmentController::REVIEW_ACCEPT,
            'application' => '666',
            'licence' => '666',
            'flags' => ['PASS', 'PASS', 'PASS', 'PASS', 'PASS', 'PASS'],
        ];

        $assessmentService = m::mock(FinancialEvidenceAssessmentService::class);
        $assessmentService->expects('assess')
            ->with(9, ['FAIL', 'FAIL', 'FAIL', 'FAIL', 'FAIL', 'FAIL'], $expectedKey, 42)
            // The outcome is the service's call; the controller only reports it.
            ->andReturn(AssessmentStatus::APPROVED);

        $sut = $this->createSut($class, $flashMessenger, $this->reviewForm($post, true), $assessmentService);
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->expects('handleQuery')->andReturn($this->okResponse([
            $this->assessedAnalysis(8, 'pass'),
            $this->assessedAnalysis(9, 'fail'),
        ]));
        $sut->shouldNotReceive('handleCommand');
        $response = $this->expectRedirectToRefresh($sut);

        $this->assertSame($response, $sut->indexAction());
    }

    public static function outcomeProvider(): \Iterator
    {
        yield 'approved' => [
            AssessmentStatus::APPROVED,
            'addSuccessMessage',
            'Document review accepted: the document is approved',
        ];
        yield 'rejected' => [
            AssessmentStatus::REJECTED,
            'addWarningMessage',
            'Document review accepted: the document is rejected because one or more checks did not pass',
        ];
        yield 'not recorded' => [null, 'addErrorMessage', 'The document review could not be recorded'];
    }

    #[DataProvider('outcomeProvider')]
    public function testAcceptReportsTheOutcome(?AssessmentStatus $outcome, string $flashMethod, string $message): void
    {
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $flashMessenger->expects($flashMethod)->with($message);

        $post = ['analysisId' => '9', 'review' => AbstractFinancialEvidenceAssessmentController::REVIEW_ACCEPT];

        $assessmentService = m::mock(FinancialEvidenceAssessmentService::class);
        $assessmentService->expects('assess')->andReturn($outcome);

        $sut = $this->createSut(ApplicationController::class, $flashMessenger, $this->reviewForm($post, true), $assessmentService);
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->expects('handleQuery')->andReturn($this->okResponse([$this->assessedAnalysis(9, 'pass')]));
        $response = $this->expectRedirectToRefresh($sut);

        $this->assertSame($response, $sut->indexAction());
    }

    /** An analysis that is not on this page (another application's, or not successful) is not assessed. */
    public function testAcceptForAnAnalysisNotOnThePageAssessesNothing(): void
    {
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $flashMessenger->expects('addUnknownError');

        $post = ['analysisId' => '9', 'review' => AbstractFinancialEvidenceAssessmentController::REVIEW_ACCEPT];

        $assessmentService = m::mock(FinancialEvidenceAssessmentService::class);
        $assessmentService->shouldNotReceive('assess');

        $sut = $this->createSut(ApplicationController::class, $flashMessenger, $this->reviewForm($post, true), $assessmentService);
        $sut->allows('getIdentifier')->andReturn(42);
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->expects('handleQuery')->andReturn($this->okResponse([$this->assessedAnalysis(8, 'pass')]));
        $response = $this->expectRedirectToRefresh($sut);

        $this->assertSame($response, $sut->indexAction());
    }

    public function testAcceptWithoutAnAssessmentAssessesNothing(): void
    {
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $flashMessenger->expects('addErrorMessage')->with('This document has no assessment to review');

        $post = ['analysisId' => '9', 'review' => AbstractFinancialEvidenceAssessmentController::REVIEW_ACCEPT];

        $assessmentService = m::mock(FinancialEvidenceAssessmentService::class);
        $assessmentService->shouldNotReceive('assess');

        $sut = $this->createSut(ApplicationController::class, $flashMessenger, $this->reviewForm($post, true), $assessmentService);
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

    /** A post the page did not build assesses nothing. */
    #[DataProvider('rejectedPostProvider')]
    public function testRejectedPostAssessesNothing(array $post, bool $formIsValid): void
    {
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $flashMessenger->expects('addUnknownError');

        $assessmentService = m::mock(FinancialEvidenceAssessmentService::class);
        $assessmentService->shouldNotReceive('assess');

        $sut = $this->createSut(
            ApplicationController::class,
            $flashMessenger,
            $this->reviewForm($post, $formIsValid),
            $assessmentService
        );
        $sut->allows('getRequest')->andReturn($this->postRequest($post));
        $sut->shouldNotReceive('handleQuery');
        $response = $this->expectRedirectToRefresh($sut);

        $this->assertSame($response, $sut->indexAction());
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
        $container->allows('get')->with('navigation')->andReturn([]);
        $container->expects('get')->with(FinancialEvidenceAssessmentService::class)
            ->andReturn(m::mock(FinancialEvidenceAssessmentService::class));

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

    /**
     * @param class-string<AbstractFinancialEvidenceAssessmentController> $class
     */
    private function createSut(
        string $class,
        ?FlashMessengerHelperService $flashMessenger = null,
        ?Form $reviewForm = null,
        ?FinancialEvidenceAssessmentService $assessmentService = null
    ): AbstractFinancialEvidenceAssessmentController|m\MockInterface {
        // The review form comes from the form helper, which adds its CSRF element.
        $formHelper = m::mock(FormHelperService::class);
        $formHelper->allows('createForm')
            ->with(FinancialEvidenceAssessmentReview::class, true, false)
            ->andReturn($reviewForm ?? m::mock(Form::class));

        return m::mock($class, [
            m::mock(NiTextTranslation::class),
            m::mock(AuthorizationService::class),
            new StringHelperService(),
            m::mock(RestrictionHelperService::class),
            $flashMessenger ?? m::mock(FlashMessengerHelperService::class),
            $formHelper,
            [],
            $assessmentService ?? m::mock(FinancialEvidenceAssessmentService::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();
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

    private function okResponse(array $analyses): CqrsResponse
    {
        $response = m::mock(CqrsResponse::class);
        $response->allows('isOk')->andReturnTrue();
        $response->allows('getResult')->andReturn(['analyses' => $analyses]);

        return $response;
    }
}
