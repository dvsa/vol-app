<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Letter;

use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\UpdateIssues;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceIssue\UpdateContent as UpdateIssueContent;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo\CreateCustom;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo\DeleteCustom;
use Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\GenerationContext;
use Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\PreviouslySent;
use Dvsa\Olcs\Transfer\Query\Letter\LetterIssue\GetList as LetterIssueList;
use Dvsa\Olcs\Transfer\Query\Letter\LetterIssueType\GetList as LetterIssueTypeList;
use Laminas\Http\Request;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Letter\LetterGenerationController as Sut;
use Olcs\Mvc\Controller\Plugin\Placeholder;
use Olcs\Mvc\Controller\Plugin\ViewBuilder;

/**
 * Covers the radio "pick one" letter-choice validation (VOL-7282/VOL-7303).
 */
final class LetterGenerationControllerTest extends MockeryTestCase
{
    private function bareSut(): Sut
    {
        return m::mock(Sut::class, [
            m::mock(TranslationHelperService::class),
            m::mock(FormHelperService::class),
            m::mock(FlashMessengerHelperService::class),
            m::mock(Navigation::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();
    }

    private function okResponse(array $result): Response
    {
        $response = m::mock(Response::class);
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($result);

        return $response;
    }

    private function makeSut(array $letterChoices): Sut
    {
        $sut = $this->bareSut();

        $sut->shouldReceive('fetchLetterChoicesForLetterType')->andReturn($letterChoices);

        return $sut;
    }

    private function validate(Sut $sut, array $selectedChoices): ?string
    {
        $method = new \ReflectionMethod(Sut::class, 'validateRequiredRadioChoices');

        return $method->invoke($sut, 1, $selectedChoices);
    }

    public function testCheckboxOnlyChoicesAreNeverRejected(): void
    {
        $sut = $this->makeSut([
            ['id' => 10, 'label' => 'Is Final', 'groupLabel' => 'Other', 'inputType' => 'checkbox'],
        ]);

        $this->assertNull($this->validate($sut, []));
        $this->assertNull($this->validate($sut, ['10']));
    }

    public function testRadioGroupWithExactlyOneSelectionPasses(): void
    {
        $sut = $this->makeSut([
            ['id' => 1, 'label' => 'England', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
            ['id' => 2, 'label' => 'Scotland', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
            ['id' => 3, 'label' => 'Wales', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
        ]);

        $this->assertNull($this->validate($sut, ['2']));
    }

    public function testRadioGroupWithNoSelectionIsRejected(): void
    {
        $sut = $this->makeSut([
            ['id' => 1, 'label' => 'England', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
            ['id' => 2, 'label' => 'Scotland', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
        ]);

        $error = $this->validate($sut, []);
        $this->assertNotNull($error);
        $this->assertStringContainsString('Nation', $error);
    }

    public function testRadioGroupWithMultipleSelectionsIsRejected(): void
    {
        $sut = $this->makeSut([
            ['id' => 1, 'label' => 'England', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
            ['id' => 2, 'label' => 'Scotland', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
        ]);

        $this->assertNotNull($this->validate($sut, ['1', '2']));
    }

    public function testMultipleRadioGroupsEachNeedExactlyOne(): void
    {
        $sut = $this->makeSut([
            ['id' => 1, 'label' => 'England', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
            ['id' => 2, 'label' => 'Scotland', 'groupLabel' => 'Nation', 'inputType' => 'radio'],
            ['id' => 5, 'label' => 'First', 'groupLabel' => 'Stage', 'inputType' => 'radio'],
            ['id' => 6, 'label' => 'Final', 'groupLabel' => 'Stage', 'inputType' => 'radio'],
            ['id' => 9, 'label' => 'Is urgent', 'groupLabel' => 'Other', 'inputType' => 'checkbox'],
        ]);

        // Both groups satisfied (checkbox ignored)
        $this->assertNull($this->validate($sut, ['1', '6', '9']));
        // Second group ("Stage") unsatisfied
        $error = $this->validate($sut, ['1']);
        $this->assertNotNull($error);
        $this->assertStringContainsString('Stage', $error);
    }

    private function editorContent(mixed ...$candidates): string
    {
        $method = new \ReflectionMethod(Sut::class, 'editorContent');

        return $method->invoke($this->makeSut([]), ...$candidates);
    }

    public function testEditorContentPrefersEditedThenGeneratedThenDefault(): void
    {
        $edited = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'edited']]]];
        $generated = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'ACME LTD']]]];
        $default = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => '[[OP_NAME_ONLY]]']]]];

        $this->assertSame(json_encode($edited), $this->editorContent($edited, $generated, $default));
        $this->assertSame(json_encode($generated), $this->editorContent(null, $generated, $default));
        $this->assertSame(json_encode($default), $this->editorContent(null, null, $default));
    }

    public function testEditorContentPassesJsonStringsThroughAndFallsBackToAnEmptyDocument(): void
    {
        $this->assertSame('{"blocks":[]}', $this->editorContent(null, '{"blocks":[]}', ['blocks' => ['x']]));
        $this->assertSame(
            json_encode(['blocks' => [], 'version' => '2.28.2']),
            $this->editorContent(null, null, null)
        );
    }

    private function invokeProtected(string $method, mixed ...$args): mixed
    {
        return (new \ReflectionMethod(Sut::class, $method))->invoke($this->makeSut([]), ...$args);
    }

    /**
     * VOL-7737: on the edit page each to-do sits under the first issue it can go under that is
     * on screen; one with none of its issues on screen is listed after them.
     */
    public function testEditPageTodosSitUnderTheFirstOfTheirIssuesOnScreen(): void
    {
        $groupedIssues = [
            1 => ['typeName' => 'Finances', 'issues' => [['id' => 10], ['id' => 20]]],
        ];
        $todos = [
            ['id' => 1, 'placementIssueIds' => [10, 20]],
            ['id' => 2, 'placementIssueIds' => [30, 20]],
            ['id' => 3, 'placementIssueIds' => [30]],
            ['id' => 4],
        ];

        [$placed, $unplaced] = $this->invokeProtected('placeTodosUnderIssues', $groupedIssues, $todos);

        $this->assertSame([1], array_column($placed[1]['issues'][0]['todos'], 'id'));
        $this->assertSame([2], array_column($placed[1]['issues'][1]['todos'], 'id'));
        $this->assertSame([3, 4], array_column($unplaced, 'id'));
    }

    private function instanceSection(int $id, string $key): array
    {
        return ['id' => $id, 'letterSectionVersion' => ['letterSectionVariant' => ['letterSection' => ['sectionKey' => $key]]]];
    }

    public function testEditPageFollowsTheLetterOrder(): void
    {
        $instanceSections = [
            $this->instanceSection(1, 'intro'),
            $this->instanceSection(2, '__ISSUES__'),
            $this->instanceSection(3, 'closing'),
        ];
        $intro = ['id' => 1, 'name' => 'Intro'];
        $closing = ['id' => 3, 'name' => 'Closing'];

        $this->assertSame(
            [['section' => $intro], ['issues' => true], ['section' => $closing]],
            $this->invokeProtected('editLayout', $instanceSections, [$closing, $intro])
        );
        $this->assertSame(
            [['issues' => true], ['section' => $closing]],
            $this->invokeProtected('editLayout', $instanceSections, [$closing])
        );
    }

    public function testEditPageShowsIssuesAfterTheSectionsWhenTheLetterHasNoIssuesSection(): void
    {
        $intro = ['id' => 1, 'name' => 'Intro'];

        $this->assertSame(
            [['section' => $intro], ['issues' => true]],
            $this->invokeProtected('editLayout', [$this->instanceSection(1, 'intro')], [$intro])
        );
    }

    public function testFetchLetterChoicesSortsByDisplayOrder(): void
    {
        // VOL-7282: admin sets First request = 1, Final request = 2, but the modal
        // showed them in insertion order.
        $sut = m::mock(Sut::class, [
            m::mock(TranslationHelperService::class),
            m::mock(FormHelperService::class),
            m::mock(FlashMessengerHelperService::class),
            m::mock(Navigation::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('fetchTemplateById')->with(1)->andReturn(['letterType' => ['id' => 7]]);

        $response = m::mock();
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn([
            'letterTypeChoices' => [
                ['letterChoice' => ['id' => 20, 'label' => 'Final request', 'groupLabel' => 'First or final request', 'inputType' => 'radio', 'displayOrder' => 2, 'isActive' => true]],
                ['letterChoice' => ['id' => 10, 'label' => 'First request', 'groupLabel' => 'First or final request', 'inputType' => 'radio', 'displayOrder' => 1, 'isActive' => true]],
            ],
        ]);
        $sut->shouldReceive('handleQuery')->andReturn($response);

        $method = new \ReflectionMethod(Sut::class, 'fetchLetterChoicesForLetterType');
        $choices = $method->invoke($sut, 1);

        $this->assertSame(['First request', 'Final request'], array_column($choices, 'label'));
    }

    private function todosList(array $letterInstanceTodos): array
    {
        $method = new \ReflectionMethod(Sut::class, 'buildTodosList');

        return $method->invoke($this->makeSut([]), ['letterInstanceTodos' => $letterInstanceTodos]);
    }

    private function instanceTodo(bool $requiresInput, ?array $editedDescription): array
    {
        return [
            'id' => 3,
            'editedDescription' => $editedDescription,
            // the API works this out (LetterInstanceTodo::isInputPending, VOL-7735)
            'inputPending' => $requiresInput && $editedDescription === null,
            'requiringIssueCount' => 2,
            'letterTodoVersion' => [
                'name' => 'Upload bank statements',
                'requiresInput' => $requiresInput,
                'letterTodo' => ['todoKey' => 'FI01'],
            ],
        ];
    }

    public function testATodoNeedingInputIsFlaggedUntilEdited(): void
    {
        $todos = $this->todosList([$this->instanceTodo(true, null)]);

        $this->assertTrue($todos[0]['inputPending']);
        $this->assertSame('Upload bank statements (FI01)', $todos[0]['name']);
        $this->assertSame(2, $todos[0]['requiringIssueCount']);
    }

    public function testATodoNeedingInputIsNotFlaggedOnceEdited(): void
    {
        $edited = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'By 1 October']]]];

        $this->assertFalse($this->todosList([$this->instanceTodo(true, $edited)])[0]['inputPending']);
    }

    public function testATodoThatDoesNotNeedInputIsNeverFlagged(): void
    {
        $this->assertFalse($this->todosList([$this->instanceTodo(false, null)])[0]['inputPending']);
    }

    /**
     * Adverts is goods only, Finance has one issue for both and one PSV only. The PSV one
     * comes back as a bare id to cover both shapes the API can serialise a RefData in.
     */
    private function accordionFor(?string $goodsOrPsv): array
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchActiveIssueTypes')->andReturn([
            ['id' => 1, 'name' => 'Adverts', 'isActive' => true],
            ['id' => 2, 'name' => 'Finance', 'isActive' => true],
        ]);
        $sut->shouldReceive('fetchActiveLetterIssues')->andReturn([
            ['id' => 10, 'currentVersion' => ['letterIssueType' => ['id' => 1], 'goodsOrPsv' => ['id' => 'lcat_gv']]],
            ['id' => 20, 'currentVersion' => ['letterIssueType' => ['id' => 2], 'goodsOrPsv' => null]],
            ['id' => 21, 'currentVersion' => ['letterIssueType' => ['id' => 2], 'goodsOrPsv' => 'lcat_psv']],
        ]);

        $method = new \ReflectionMethod(Sut::class, 'buildAccordionData');
        $accordion = $method->invoke($sut, $goodsOrPsv);

        $issueIds = [];
        foreach ($accordion as $section) {
            $issueIds[$section['issueType']['name']] = array_column($section['issues'], 'id');
        }

        return $issueIds;
    }

    /**
     * VOL-7735: the Other option comes last in its group, whatever its key sorts as ("FI-OTHER"
     * sorts before "FI01", since "-" comes before the digits).
     */
    public function testOtherIssueIsLastInItsGroup(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchActiveIssueTypes')->andReturn([['id' => 2, 'name' => 'Finance', 'isActive' => true]]);
        $sut->shouldReceive('fetchActiveLetterIssues')->andReturn([
            ['id' => 30, 'issueKey' => 'FI-OTHER', 'currentVersion' => ['letterIssueType' => ['id' => 2], 'isOther' => true]],
            ['id' => 20, 'issueKey' => 'FI01', 'currentVersion' => ['letterIssueType' => ['id' => 2]]],
            ['id' => 21, 'issueKey' => 'FI02', 'currentVersion' => ['letterIssueType' => ['id' => 2], 'isOther' => false]],
        ]);

        $accordion = (new \ReflectionMethod(Sut::class, 'buildAccordionData'))->invoke($sut, null);

        $this->assertSame([20, 21, 30], array_column($accordion[0]['issues'], 'id'));
    }

    public function testPsvContextHidesGoodsOnlyIssuesAndLeavesTheirTypeEmpty(): void
    {
        $this->assertSame(['Adverts' => [], 'Finance' => [20, 21]], $this->accordionFor('lcat_psv'));
    }

    public function testGoodsContextKeepsGoodsOnlyIssues(): void
    {
        $this->assertSame(['Adverts' => [10], 'Finance' => [20]], $this->accordionFor('lcat_gv'));
    }

    public function testUnknownContextShowsEveryIssue(): void
    {
        $this->assertSame(['Adverts' => [10], 'Finance' => [20, 21]], $this->accordionFor(null));
    }

    private function runCreateAction(Sut $sut, array $query): mixed
    {
        $request = new Request();
        $request->getQuery()->fromArray($query);

        $placeholder = m::mock(Placeholder::class);
        $placeholder->shouldReceive('setPlaceholder');

        $viewBuilder = m::mock(ViewBuilder::class);
        $viewBuilder->shouldReceive('buildView')->andReturnUsing(fn(ViewModel $view) => $view);

        $sut->shouldReceive('getRequest')->andReturn($request);
        $sut->shouldReceive('extractRouteParams')->andReturn([]);
        $sut->shouldReceive('placeholder')->andReturn($placeholder);
        $sut->shouldReceive('viewBuilder')->andReturn($viewBuilder);
        $sut->shouldReceive('fetchAppendicesForLetterType')->andReturn([]);
        $sut->shouldReceive('fetchPreviousLetters')->andReturn([])->byDefault();
        $sut->shouldReceive('csrfToken')->andReturn('csrf-hash')->byDefault();

        return $sut->createAction();
    }

    public function testCreateActionFiltersIssuesByTheLicenceGoodsOrPsv(): void
    {
        $sut = $this->bareSut();

        $sut->shouldReceive('handleQuery')
            ->with(m::on(fn($query) => $query instanceof GenerationContext && $query->getLicence() === 7))
            ->once()
            ->andReturn($this->okResponse(['goodsOrPsv' => 'lcat_psv', 'isNi' => false]));
        $sut->shouldReceive('buildAccordionData')->with('lcat_psv')->once()->andReturn([]);
        $sut->shouldReceive('fetchLetterChoicesForLetterType')->with(5, 'lcat_psv')->once()->andReturn([]);

        $this->runCreateAction($sut, ['template' => '5', 'licence' => '7']);
    }

    public function testCreateActionWithoutAnEntityShowsEverything(): void
    {
        $sut = $this->bareSut();

        $sut->shouldReceive('handleQuery')->never();
        $sut->shouldReceive('buildAccordionData')->with(null)->once()->andReturn([]);
        $sut->shouldReceive('fetchLetterChoicesForLetterType')->with(5, null)->once()->andReturn([]);

        $this->runCreateAction($sut, ['template' => '5']);
    }

    /**
     * Form posts without a valid "security" token are turned into GETs by Common\Module, so the
     * create form has to carry one. Without it every new letter failed that check.
     */
    public function testCreateLetterFormCarriesACsrfToken(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('buildAccordionData')->andReturn([]);
        $sut->shouldReceive('fetchLetterChoicesForLetterType')->andReturn([]);
        $sut->shouldReceive('csrfToken')->once()->andReturn('abc123-token');

        $view = $this->runCreateAction($sut, ['template' => '5']);

        $this->assertSame('abc123-token', $view->getVariable('csrfToken'));
    }

    /**
     * The form is written by hand, so nothing puts the token on it for us: it has to be in the
     * form that the dialog posts
     */
    public function testCreateLetterFormPostsTheCsrfToken(): void
    {
        $renderer = new PhpRenderer();
        $renderer->setResolver(new TemplatePathStack([
            'script_paths' => [__DIR__ . '/../../../../../module/Olcs/view'],
        ]));

        $html = $renderer->render('pages/letter/create', [
            'templateId' => 5,
            'queryParams' => ['template' => 5],
            'accordionData' => [],
            'appendicesData' => [],
            'letterChoicesData' => [],
            'csrfToken' => 'abc123-token',
        ]);

        $page = new \DOMDocument();
        $page->loadHTML($html, LIBXML_NOERROR);
        $tokens = (new \DOMXPath($page))->query('//form[@id="letter-create-form"]//input[@name="security"]');

        $this->assertSame(1, $tokens->length);
        $this->assertSame('abc123-token', $tokens->item(0)->getAttribute('value'));
    }

    /**
     * The New letter form needs its template for the appendices, the choices and the letters
     * already sent. It's asked for once, not once for each.
     */
    public function testTheTemplateIsOnlyFetchedOnceForARequest(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('handleQuery')
            ->with(m::type(\Dvsa\Olcs\Transfer\Query\DocTemplate\ById::class))
            ->once()
            ->andReturn($this->okResponse(['id' => 5, 'letterType' => ['id' => 7]]));

        $this->assertSame(7, $this->invokeProtectedOn($sut, 'fetchTemplateById', 5)['letterType']['id']);
        $this->assertSame(7, $this->invokeProtectedOn($sut, 'fetchTemplateById', 5)['letterType']['id']);
    }

    /**
     * Starting a letter offers the letters of that type already sent for the same application or
     * licence, so a final request can start from the issues its first request covered
     */
    public function testCreateActionOffersLettersAlreadySent(): void
    {
        $sent = [['id' => 392, 'label' => '11/11/2026 16:24 - First request - 2 issues', 'issueIds' => [15, 1]]];

        $sut = $this->bareSut();
        $sut->shouldReceive('fetchLetterContext')->andReturn([]);
        $sut->shouldReceive('buildAccordionData')->andReturn([]);
        $sut->shouldReceive('fetchLetterChoicesForLetterType')->andReturn([]);
        $sut->shouldReceive('fetchPreviousLetters')->with(5, ['type' => 'application', 'id' => 8])->once()->andReturn($sent);

        $view = $this->runCreateAction($sut, ['template' => '5', 'application' => '8']);

        $this->assertSame($sent, $view->getVariable('previousLetters'));
    }

    private function sentLetter(array $overrides = []): array
    {
        return $overrides + [
            'id' => 392,
            'sentOn' => '2026-11-11T16:24:18+0000',
            'choices' => ['First request', 'Interim'],
            'issueIds' => [15, 1],
        ];
    }

    public function testPreviousLettersAreLookedUpByLetterTypeAndWhatTheLetterIsRaisedOn(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchTemplateById')->with(5)->andReturn(['letterType' => ['id' => 7]]);
        $sut->shouldReceive('handleQuery')
            ->with(m::on(
                static fn($query): bool => $query instanceof PreviouslySent
                    && $query->getLetterType() === 7
                    && $query->getApplication() === 8
                    && $query->getLicence() === null
            ))
            ->once()
            // the API hands a list back as "results", whatever the handler called it
            ->andReturn($this->okResponse(['count' => 3, 'results' => [
                $this->sentLetter(),
                $this->sentLetter(['id' => 388, 'sentOn' => '2026-11-10T09:05:00+0000', 'choices' => ['Final request'], 'issueIds' => [5]]),
                $this->sentLetter(['id' => 385, 'sentOn' => '2026-11-09T12:00:00+0000', 'choices' => [], 'issueIds' => []]),
            ]]));

        $this->assertSame(
            [
                ['id' => 392, 'label' => '11/11/2026 16:24 - First request, Interim - 2 issues', 'issueIds' => [15, 1]],
                ['id' => 388, 'label' => '10/11/2026 09:05 - Final request - 1 issue', 'issueIds' => [5]],
                // sent with appendices only
                ['id' => 385, 'label' => '09/11/2026 12:00 - 0 issues', 'issueIds' => []],
            ],
            $this->invokeProtectedOn($sut, 'fetchPreviousLetters', 5, ['type' => 'application', 'id' => 8])
        );
    }

    /**
     * The picker is a convenience. If its lookup fails (an API a release behind doesn't know the
     * query yet, and answers 404) the New letter form still has to open, just without it.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('failedLookups')]
    public function testAFailedLookupOfPreviousLettersDoesNotStopTheForm(\Throwable $failure): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchTemplateById')->andReturn(['letterType' => ['id' => 7]]);
        $sut->shouldReceive('handleQuery')->with(m::type(PreviouslySent::class))->once()->andThrow($failure);

        $this->assertSame([], $this->invokeProtectedOn($sut, 'fetchPreviousLetters', 5, ['type' => 'application', 'id' => 8]));
    }

    public static function failedLookups(): \Iterator
    {
        yield 'not found' => [new \Common\Service\Cqrs\Exception\NotFoundException('API responded with a 404 Not Found')];
        yield 'not allowed' => [new \Common\Service\Cqrs\Exception\AccessDeniedException('Forbidden')];
        yield 'server error' => [new \Common\Service\Cqrs\Exception('Internal Server Error')];
    }

    /**
     * Not every letter is raised on something (the route can be opened bare), and not every
     * template is a letter builder one. Either way there are no earlier letters to offer.
     */
    public function testNoPreviousLettersWithoutAnEntityOrALetterType(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchTemplateById')->andReturn(['letterType' => null]);
        $sut->shouldNotReceive('handleQuery');

        $this->assertSame([], $this->invokeProtectedOn($sut, 'fetchPreviousLetters', 5, []));
        $this->assertSame([], $this->invokeProtectedOn($sut, 'fetchPreviousLetters', 5, ['type' => 'licence', 'id' => 7]));
    }

    private function renderCreateForm(array $previousLetters): \DOMXPath
    {
        $renderer = new PhpRenderer();
        $renderer->setResolver(new TemplatePathStack([
            'script_paths' => [__DIR__ . '/../../../../../module/Olcs/view'],
        ]));

        $page = new \DOMDocument();
        $page->loadHTML($renderer->render('pages/letter/create', [
            'templateId' => 5,
            'queryParams' => ['template' => 5],
            'accordionData' => [],
            'appendicesData' => [],
            'letterChoicesData' => [],
            'csrfToken' => 'abc123-token',
            'previousLetters' => $previousLetters,
        ]), LIBXML_NOERROR);

        return new \DOMXPath($page);
    }

    /**
     * Each letter already sent is an option carrying the issues to tick (letter-generation.js
     * does the ticking). The picker only chooses ticks, so it isn't posted with the form.
     */
    public function testCreateLetterFormListsLettersAlreadySent(): void
    {
        $form = $this->renderCreateForm([
            ['id' => 392, 'label' => '11/11/2026 16:24 - First request - 2 issues', 'issueIds' => [15, 1]],
            ['id' => 388, 'label' => '10/11/2026 09:05 - Final request - 1 issue', 'issueIds' => [5]],
        ]);

        $options = $form->query('//form[@id="letter-create-form"]//select[@id="previous-letter"]/option');
        $this->assertSame(3, $options->length);
        $this->assertSame('', $options->item(0)->getAttribute('value'));
        $this->assertSame('15,1', $options->item(1)->getAttribute('data-issue-ids'));
        $this->assertSame('11/11/2026 16:24 - First request - 2 issues', trim($options->item(1)->textContent));
        $this->assertSame('5', $options->item(2)->getAttribute('data-issue-ids'));
        $this->assertSame(0, $form->query('//select[@id="previous-letter"][@name]')->length);
    }

    public function testCreateLetterFormHasNoPickerWhenNothingHasBeenSent(): void
    {
        $this->assertSame(0, $this->renderCreateForm([])->query('//select[@id="previous-letter"]')->length);
    }

    /**
     * Hands back $total rows in pages of 100, the most the list queries allow
     */
    private function pagedSut(string $queryClass, int $total, array $row): Sut
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('handleQuery')
            ->with(m::type($queryClass))
            ->andReturnUsing(function ($query) use ($total, $row) {
                $this->assertSame(100, (int) $query->getLimit());
                $offset = ((int) $query->getPage() - 1) * 100;
                $rows = [];
                for ($i = $offset + 1; $i <= min($total, $offset + 100); $i++) {
                    $rows[] = ['id' => $i] + $row;
                }

                return $this->okResponse(['results' => $rows, 'count' => $total]);
            });

        return $sut;
    }

    public function testEveryLetterIssueIsLoadedNotJustTheFirstHundred(): void
    {
        $sut = $this->pagedSut(LetterIssueList::class, 130, []);

        $method = new \ReflectionMethod(Sut::class, 'fetchActiveLetterIssues');

        $this->assertSame(range(1, 130), array_column($method->invoke($sut), 'id'));
    }

    public function testEveryIssueTypeIsLoadedNotJustTheFirstHundred(): void
    {
        $sut = $this->pagedSut(LetterIssueTypeList::class, 101, ['isActive' => true]);

        $method = new \ReflectionMethod(Sut::class, 'fetchActiveIssueTypes');

        $this->assertSame(range(1, 101), array_column($method->invoke($sut), 'id'));
    }

    public function testFetchLetterChoicesDropsChoicesForTheOtherLicenceType(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchTemplateById')->with(1)->andReturn(['letterType' => ['id' => 7]]);
        $sut->shouldReceive('handleQuery')->andReturn($this->okResponse([
            'letterTypeChoices' => [
                ['letterChoice' => ['id' => 1, 'label' => 'Interims', 'isActive' => true, 'goodsOrPsv' => ['id' => 'lcat_gv']]],
                ['letterChoice' => ['id' => 2, 'label' => 'Is final', 'isActive' => true, 'goodsOrPsv' => null]],
            ],
        ]));

        $method = new \ReflectionMethod(Sut::class, 'fetchLetterChoicesForLetterType');

        $this->assertSame(['Is final'], array_column($method->invoke($sut, 1, 'lcat_psv'), 'label'));
        $this->assertSame(['Interims', 'Is final'], array_column($method->invoke($sut, 1, 'lcat_gv'), 'label'));
        $this->assertSame(['Interims', 'Is final'], array_column($method->invoke($sut, 1, null), 'label'));
    }

    public function testRadioGroupHiddenByGoodsOrPsvIsNotDemanded(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchLetterChoicesForLetterType')->with(1, 'lcat_psv')->andReturn([]);

        $method = new \ReflectionMethod(Sut::class, 'validateRequiredRadioChoices');

        $this->assertNull($method->invoke($sut, 1, [], 'lcat_psv'));
    }

    public function testGenerateActionChecksRadioGroupsAgainstTheLicenceGoodsOrPsv(): void
    {
        $sut = $this->bareSut();

        $request = new Request();
        $request->setMethod('POST');
        $request->getPost()->fromArray(['letterType' => '5']);
        $request->getQuery()->fromArray(['licence' => '7']);
        $sut->shouldReceive('getRequest')->andReturn($request);
        $sut->shouldReceive('extractRouteParams')->andReturn([]);
        $sut->shouldReceive('fetchTemplateById')->with(5)->andReturn(['letterType' => ['id' => 3]]);
        $sut->shouldReceive('fetchLetterContext')
            ->with(['type' => 'licence', 'id' => 7])
            ->andReturn(['goodsOrPsv' => 'lcat_psv', 'isNi' => false]);

        // The goods only "pick one" group isn't on a PSV letter, so nothing has to be picked
        $sut->shouldReceive('fetchLetterChoicesForLetterType')->with(5, 'lcat_psv')->once()->andReturn([]);

        $failed = m::mock(Response::class);
        $failed->shouldReceive('isOk')->andReturn(false);
        $failed->shouldReceive('getResult')->andReturn(['messages' => ['stop here']]);
        $sut->shouldReceive('handleCommand')->once()->andReturn($failed);

        $response = $sut->generateAction();

        $this->assertStringContainsString('Failed to generate letter', $response->getContent());
    }

    /**
     * The dialog shows the caseworker why a letter couldn't be created, so the reasons have to be
     * words. They used to be sent as JSON, brackets and all.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('refusedLetters')]
    public function testARefusedLetterSaysWhyInWords(array $messages, string $expected): void
    {
        $sut = $this->bareSut();

        $request = new Request();
        $request->setMethod('POST');
        $request->getPost()->fromArray(['letterType' => '5', 'letterIssues' => ['20']]);
        $sut->shouldReceive('getRequest')->andReturn($request);
        $sut->shouldReceive('getResponse')->andReturn(new \Laminas\Http\Response());
        $sut->shouldReceive('extractRouteParams')->andReturn([]);
        $sut->shouldReceive('fetchTemplateById')->andReturn(['letterType' => ['id' => 3]]);
        $sut->shouldReceive('fetchLetterChoicesForLetterType')->andReturn([]);

        $refused = m::mock(Response::class);
        $refused->shouldReceive('isOk')->andReturn(false);
        $refused->shouldReceive('getResult')->andReturn(['messages' => $messages]);
        $sut->shouldReceive('handleCommand')->once()->andReturn($refused);

        $response = $sut->generateAction();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame($expected, json_decode($response->getContent(), true)['message']);
    }

    public static function refusedLetters(): array
    {
        return [
            'one reason' => [['Choose an issue'], 'Failed to generate letter: Choose an issue'],
            'reasons nested by field' => [
                ['selectedIssues' => ['Choose an issue'], 'licence' => ['Not found']],
                'Failed to generate letter: Choose an issue, Not found',
            ],
            'no reason given' => [[], 'Failed to generate letter'],
        ];
    }

    /**
     * What Common\Module leaves behind when a form post fails the "security" check: the request is
     * now a GET, its fields are still on it, and the response is marked
     */
    private function failedSecurityCheck(Sut $sut, array $post): void
    {
        $request = new Request();
        $request->getPost()->fromArray($post);
        $response = new \Laminas\Http\Response();
        $response->getHeaders()->addHeaderLine(\Common\Module::CSRF_ERROR_HEADER, '1');

        $sut->shouldReceive('getRequest')->andReturn($request);
        $sut->shouldReceive('getResponse')->andReturn($response);
    }

    /**
     * The fields left on a post that failed the check used to be enough to create the letter
     */
    public function testGenerateRefusesAPostThatFailedTheSecurityCheck(): void
    {
        $sut = $this->bareSut();
        $this->failedSecurityCheck($sut, ['letterType' => '5', 'letterIssues' => ['20']]);
        $sut->shouldReceive('fetchTemplateById')->never();
        $sut->shouldReceive('handleCommand')->never();

        $response = $sut->generateAction();

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(
            'This form has expired. Close it and open it again.',
            json_decode($response->getContent(), true)['message']
        );
    }

    public function testGenerateRejectsGet(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('getRequest')->andReturn(new Request());
        $sut->shouldReceive('getResponse')->andReturn(new \Laminas\Http\Response());
        $sut->shouldReceive('handleCommand')->never();

        $response = $sut->generateAction();

        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('Method not allowed', json_decode($response->getContent(), true)['message']);
    }

    private function letterForChangeIssues(array $overrides = []): array
    {
        return $overrides + [
            'id' => 55,
            'status' => ['id' => 'ltr_sts_draft'],
            'isOpenForChanges' => true,
            'isSent' => false,
            'licence' => ['id' => 7],
            'application' => null,
            'letterInstanceAppendices' => [],
            'letterInstanceIssues' => [
                [
                    'id' => 501,
                    'letterIssueVersion' => [
                        'heading' => 'Bank statements',
                        'letterIssue' => ['id' => 20],
                        'letterIssueType' => ['id' => 2, 'name' => 'Finance'],
                    ],
                ],
            ],
        ];
    }

    private function runChangeIssues(Sut $sut, array $query): mixed
    {
        $request = new Request();
        $request->getQuery()->fromArray($query);
        $sut->shouldReceive('getRequest')->andReturn($request);
        $sut->shouldReceive('getResponse')->andReturn(new \Laminas\Http\Response());
        $sut->shouldReceive('csrfToken')->andReturn('csrf-hash')->byDefault();

        return $sut->changeIssuesAction();
    }

    private function financeAccordion(): array
    {
        return [
            ['issueType' => ['id' => 1, 'name' => 'Adverts'], 'issues' => [['id' => 10, 'issueKey' => 'AD1', 'currentVersion' => ['heading' => 'Advert']]]],
            ['issueType' => ['id' => 2, 'name' => 'Finance'], 'issues' => [
                ['id' => 20, 'issueKey' => 'FI1', 'currentVersion' => ['heading' => 'Bank statements']],
                ['id' => 21, 'issueKey' => 'FI2', 'currentVersion' => ['heading' => 'Overdraft']],
            ]],
        ];
    }

    public function testChangeIssuesPreTicksIssuesOnTheLetter(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchLetterInstanceById')->with(55)->andReturn($this->letterForChangeIssues());
        $sut->shouldReceive('fetchLetterContext')->andReturn(['goodsOrPsv' => 'lcat_psv']);
        $sut->shouldReceive('buildAccordionData')->andReturn($this->financeAccordion());

        $view = $this->runChangeIssues($sut, ['id' => '55', 'from' => 'edit']);

        $this->assertInstanceOf(ViewModel::class, $view);
        $this->assertTrue($view->terminate());
        $this->assertSame('pages/letter/change-issues', $view->getTemplate());
        $this->assertSame([20], $view->getVariable('selectedIssueIds'));
        $this->assertSame('edit', $view->getVariable('from'));
        $this->assertSame(55, $view->getVariable('letterInstanceId'));
        $this->assertFalse($view->getVariable('hasAppendices'));
        $this->assertFalse($view->getVariable('locked'));
    }

    public function testChangeIssuesShowsAnIssueNoLongerOffered(): void
    {
        $letter = $this->letterForChangeIssues();
        $letter['letterInstanceIssues'][] = ['id' => 502, 'letterIssueVersion' => [
            'heading' => 'Retired wording',
            'letterIssue' => ['id' => 99],
            'letterIssueType' => ['id' => 2, 'name' => 'Finance'],
        ]];
        $letter['letterInstanceIssues'][] = ['id' => 503, 'letterIssueVersion' => [
            'heading' => 'Orphaned',
            'letterIssue' => ['id' => 77],
            'letterIssueType' => ['id' => 5, 'name' => 'Gone type'],
        ]];

        $sut = $this->bareSut();
        $sut->shouldReceive('fetchLetterInstanceById')->andReturn($letter);
        $sut->shouldReceive('fetchLetterContext')->andReturn([]);
        $sut->shouldReceive('buildAccordionData')->andReturn($this->financeAccordion());

        $accordion = $this->runChangeIssues($sut, ['id' => '55'])->getVariable('accordionData');

        $finance = $accordion[1];
        $this->assertSame([20, 21, 99], array_column($finance['issues'], 'id'));
        $this->assertSame('Retired wording', $finance['issues'][2]['currentVersion']['heading']);
        $this->assertSame('Gone type', $accordion[2]['issueType']['name']);
        $this->assertSame([77], array_column($accordion[2]['issues'], 'id'));
    }

    /**
     * Review: an issue no longer offered still goes before its type's Other issue, which is last
     * (VOL-7735); Change issues numbers the letter in the order the modal shows
     */
    public function testAnIssueNoLongerOfferedGoesBeforeTheOtherIssue(): void
    {
        $letter = $this->letterForChangeIssues();
        $letter['letterInstanceIssues'][] = ['id' => 502, 'letterIssueVersion' => [
            'heading' => 'Retired wording',
            'letterIssue' => ['id' => 99],
            'letterIssueType' => ['id' => 2, 'name' => 'Finance'],
        ]];
        $accordion = $this->financeAccordion();
        $accordion[1]['issues'][] = ['id' => 29, 'issueKey' => 'FI-OTHER', 'currentVersion' => ['heading' => 'Other issue', 'isOther' => true]];

        $sut = $this->bareSut();
        $sut->shouldReceive('fetchLetterInstanceById')->andReturn($letter);
        $sut->shouldReceive('fetchLetterContext')->andReturn([]);
        $sut->shouldReceive('buildAccordionData')->andReturn($accordion);

        $finance = $this->runChangeIssues($sut, ['id' => '55'])->getVariable('accordionData')[1];

        $this->assertSame([20, 21, 99, 29], array_column($finance['issues'], 'id'));
    }

    public function testChangeIssuesUsesTheApplicationThenLicenceForGoodsOrPsv(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchLetterInstanceById')->andReturn(
            $this->letterForChangeIssues(['application' => ['id' => 3]])
        );
        $sut->shouldReceive('fetchLetterContext')->with(['type' => 'application', 'id' => 3])->once()
            ->andReturn(['goodsOrPsv' => 'lcat_gv']);
        $sut->shouldReceive('buildAccordionData')->with('lcat_gv')->once()->andReturn([]);

        $this->runChangeIssues($sut, ['id' => '55']);
    }

    public function testChangeIssuesOnALetterThatCanNoLongerChangeIsLocked(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchLetterInstanceById')->andReturn(
            $this->letterForChangeIssues(['status' => ['id' => 'ltr_sts_sent'], 'isOpenForChanges' => false, 'isSent' => true])
        );
        $sut->shouldReceive('buildAccordionData')->never();

        $view = $this->runChangeIssues($sut, ['id' => '55']);

        $this->assertTrue($view->getVariable('locked'));
    }

    public static function closedLetters(): \Iterator
    {
        yield 'sent' => [['status' => ['id' => 'ltr_sts_sent'], 'isOpenForChanges' => false, 'isSent' => true]];
        // review: the API's rule, not a copy of it (a deleted draft can't change either)
        yield 'cancelled' => [['status' => ['id' => 'ltr_sts_cancelled'], 'isOpenForChanges' => false, 'isSent' => false]];
        yield 'deleted draft' => [['status' => ['id' => 'ltr_sts_draft'], 'isOpenForChanges' => false, 'isSent' => false]];
    }

    /**
     * A sent letter can't be changed (VOL-7735), so its edit page sends the caseworker back to the
     * preview rather than offering editors whose saves would all be refused. No flash message is
     * set: the preview clears them, and its sidebar already says why.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('closedLetters')]
    public function testEditingALetterThatCanNoLongerChangeGoesBackToThePreview(array $letter): void
    {
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->shouldNotReceive('addErrorMessage');
        $sut = m::mock(Sut::class, [
            m::mock(TranslationHelperService::class),
            m::mock(FormHelperService::class),
            $flash,
            m::mock(Navigation::class),
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $params = m::mock(\Laminas\Mvc\Controller\Plugin\Params::class);
        $params->shouldReceive('fromQuery')->with('id')->andReturn('55');
        $params->shouldReceive('fromQuery')->with('sections', [])->andReturn(['10']);
        $params->shouldReceive('fromQuery')->andReturn([]);
        $sut->shouldReceive('params')->andReturn($params);
        $sut->shouldReceive('fetchLetterInstanceById')->with(55)->andReturn(
            $this->letterForChangeIssues($letter)
        );

        $response = new \Laminas\Http\Response();
        $redirect = m::mock(\Laminas\Mvc\Controller\Plugin\Redirect::class);
        $redirect->shouldReceive('toUrl')->with('/letter/preview?id=55')->once()->andReturn($response);
        $sut->shouldReceive('redirect')->andReturn($redirect);

        $this->assertSame($response, $sut->editAction());
    }

    public function testChangeIssuesForAMissingLetterIs404(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchLetterInstanceById')->andReturn(null);

        $this->assertSame(404, $this->runChangeIssues($sut, ['id' => '55'])->getStatusCode());
        $this->assertSame(404, $this->runChangeIssues($this->bareSut(), [])->getStatusCode());
    }

    private function postUpdateIssues(Sut $sut, array $post): array
    {
        $request = new Request();
        $request->setMethod('POST');
        $request->getPost()->fromArray($post);
        $sut->shouldReceive('getRequest')->andReturn($request);
        $sut->shouldReceive('getResponse')->andReturn(new \Laminas\Http\Response());

        $response = $sut->updateIssuesAction();

        return ['status' => $response->getStatusCode(), 'json' => json_decode($response->getContent(), true)];
    }

    public function testUpdateIssuesPostsTheSelection(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('handleCommand')
            ->with(m::on(fn($cmd) => $cmd instanceof UpdateIssues
                && $cmd->getId() === 55
                && $cmd->getSelectedIssues() === [21, 20]
                && $cmd->getConfirmLoss() === true))
            ->once()
            ->andReturn($this->okResponse(['flags' => ['addedIssueIds' => []], 'messages' => []]));
        $sut->shouldReceive('fetchLetterInstanceById')->andReturn(['letterInstanceIssues' => [], 'letterInstanceTodos' => []]);

        $result = $this->postUpdateIssues($sut, ['letterInstanceId' => '55', 'letterIssues' => ['21', '20'], 'confirmLoss' => '1']);

        $this->assertTrue($result['json']['success']);
    }

    public function testUpdateIssuesWithNothingTickedSendsAnEmptySelection(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('handleCommand')
            ->with(m::on(fn($cmd) => $cmd->getSelectedIssues() === [] && !$cmd->getConfirmLoss()))
            ->once()
            ->andReturn($this->okResponse(['flags' => [], 'messages' => []]));
        $sut->shouldReceive('fetchLetterInstanceById')->andReturn([]);

        $this->assertTrue($this->postUpdateIssues($sut, ['letterInstanceId' => '55'])['json']['success']);
    }

    public function testUpdateIssuesAsksForConfirmation(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('handleCommand')->andReturn($this->okResponse([
            'flags' => ['confirmationRequired' => true],
            'messages' => ['Bank statements', 'Send a lease'],
        ]));
        $sut->shouldReceive('fetchLetterInstanceById')->never();

        $json = $this->postUpdateIssues($sut, ['letterInstanceId' => '55', 'letterIssues' => []])['json'];

        $this->assertTrue($json['confirmationRequired']);
        $this->assertSame(['Bank statements', 'Send a lease'], $json['lostEdits']);
    }

    public function testUpdateIssuesReturnsRemainingIds(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('handleCommand')->andReturn($this->okResponse([
            'flags' => ['addedIssueIds' => [903], 'movedTodoIds' => [61 => 904]],
            'messages' => ['Issues updated: 1 added, 0 removed'],
        ]));
        $sut->shouldReceive('fetchLetterInstanceById')->with(55)->andReturn([
            'letterInstanceIssues' => [['id' => 501], ['id' => 903]],
            'letterInstanceTodos' => [['id' => 61]],
        ]);

        $json = $this->postUpdateIssues($sut, ['letterInstanceId' => '55', 'letterIssues' => ['20', '30']])['json'];

        $this->assertSame([903], $json['addedIssueIds']);
        $this->assertSame([501, 903], $json['issueIds']);
        $this->assertSame([61], $json['todoIds']);
        $this->assertSame([61 => 904], $json['movedTodoIds']);
    }

    public function testUpdateIssuesShowsTheValidationMessage(): void
    {
        $failed = m::mock(Response::class);
        $failed->shouldReceive('isOk')->andReturn(false);
        $failed->shouldReceive('getResult')->andReturn([
            'messages' => [['Issues can only be changed on a draft or ready letter']],
        ]);
        $sut = $this->bareSut();
        $sut->shouldReceive('handleCommand')->andReturn($failed);

        $result = $this->postUpdateIssues($sut, ['letterInstanceId' => '55']);

        $this->assertSame(400, $result['status']);
        $this->assertSame('Issues can only be changed on a draft or ready letter', $result['json']['message']);
    }

    public function testUpdateIssuesRejectsGet(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('getRequest')->andReturn(new Request());
        $sut->shouldReceive('getResponse')->andReturn(new \Laminas\Http\Response());
        $sut->shouldReceive('handleCommand')->never();

        $this->assertSame(405, $sut->updateIssuesAction()->getStatusCode());
    }

    /**
     * The change issues modal carries a token too, but one that has been open longer than the
     * token lasts fails the check. The caseworker is told what to do about it.
     */
    public function testUpdateIssuesSaysWhenThePageHasExpired(): void
    {
        $sut = $this->bareSut();
        $this->failedSecurityCheck($sut, ['letterInstanceId' => '55', 'letterIssues' => ['20']]);
        $sut->shouldReceive('handleCommand')->never();

        $response = $sut->updateIssuesAction();

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(
            'This form has expired. Close it and open it again.',
            json_decode($response->getContent(), true)['message']
        );
    }

    /**
     * Form posts without a valid "security" token are turned into GETs by Common\Module, so the
     * modal has to carry one or saving can never work.
     */
    public function testChangeIssuesCarriesACsrfToken(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchLetterInstanceById')->andReturn($this->letterForChangeIssues());
        $sut->shouldReceive('fetchLetterContext')->andReturn([]);
        $sut->shouldReceive('buildAccordionData')->andReturn([]);
        $sut->shouldReceive('csrfToken')->once()->andReturn('abc123-token');

        $view = $this->runChangeIssues($sut, ['id' => '55']);

        $this->assertSame('abc123-token', $view->getVariable('csrfToken'));
    }

    /**
     * VOL-7735: the sidebar names an Other issue by what the caseworker wrote, and takes
     * "Input required" from the API rather than re-deriving it from the version.
     */
    public function testSidebarUsesTheWrittenHeadingAndFlags(): void
    {
        $issues = $this->invokeProtected('buildIssuesList', ['letterInstanceIssues' => [
            ['id' => 10, 'effectiveHeading' => 'Vehicle storage', 'inputPending' => true, 'letterIssueVersion' => ['heading' => 'Other issue']],
            ['id' => 11, 'letterIssueVersion' => ['heading' => 'Bank statements too old']],
        ]]);

        $this->assertSame(['Vehicle storage', 'Bank statements too old'], array_column($issues, 'name'));
        $this->assertSame([true, false], array_column($issues, 'inputPending'));
    }

    public function testCustomTodosAreNamedByPosition(): void
    {
        $data = [
            'letterInstanceIssues' => [['id' => 10, 'effectiveHeading' => 'Vehicle storage']],
            'letterInstanceTodos' => [
                ['id' => 20, 'isCustom' => true, 'customIndex' => 2, 'customLabel' => 'Vehicle storage: to-do 2', 'inputPending' => true, 'letterInstanceIssue' => ['id' => 10]],
                ['id' => 21, 'isCustom' => false, 'letterTodoVersion' => ['name' => 'Send a lease', 'letterTodo' => ['todoKey' => 'FI02']]],
            ],
        ];

        $todos = $this->invokeProtected('buildTodosList', $data);

        $this->assertSame(['Vehicle storage: to-do 2', 'Send a lease (FI02)'], array_column($todos, 'name'));
        $this->assertSame([true, false], array_column($todos, 'inputPending'));
        $this->assertSame('To-do 2', $this->invokeProtected('todoEditorName', $data['letterInstanceTodos'][0]));
        $this->assertSame('Send a lease (FI02)', $this->invokeProtected('todoEditorName', $data['letterInstanceTodos'][1]));
    }

    private function postJson(
        Sut $sut,
        string $action,
        array $body,
        string $method = 'POST',
        string $contentType = 'application/json; charset=UTF-8'
    ): array {
        $request = new Request();
        $request->setMethod($method);
        $request->getHeaders()->addHeaderLine('Content-Type', $contentType);
        $request->setContent((string) json_encode($body));
        $sut->shouldReceive('getRequest')->andReturn($request);
        $sut->shouldReceive('getResponse')->andReturn(new \Laminas\Http\Response());

        $response = $sut->{$action . 'Action'}();

        return ['status' => $response->getStatusCode(), 'json' => json_decode($response->getContent(), true)];
    }

    public function testSaveIssueContentPassesTheHeading(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('handleCommand')
            ->with(m::on(fn($cmd) => $cmd instanceof UpdateIssueContent && $cmd->getEditedHeading() === 'Vehicle storage'))
            ->once()
            ->andReturn($this->okResponse(['messages' => ['Saved']]));

        $result = $this->postJson($sut, 'saveIssueContent', [
            'issueId' => 10, 'editedContent' => ['blocks' => []], 'version' => 1, 'editedHeading' => 'Vehicle storage',
        ]);

        $this->assertTrue($result['json']['success']);
    }

    public function testSaveIssueContentWithoutAHeadingLeavesItAlone(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('handleCommand')
            ->with(m::on(fn($cmd) => $cmd instanceof UpdateIssueContent && $cmd->getEditedHeading() === null))
            ->once()
            ->andReturn($this->okResponse(['messages' => ['Saved']]));

        $this->postJson($sut, 'saveIssueContent', ['issueId' => 10, 'editedContent' => ['blocks' => []], 'version' => 1]);
    }

    public function testAddCustomTodoReturnsTheNewId(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('handleCommand')
            ->with(m::on(fn($cmd) => $cmd instanceof CreateCustom && (int) $cmd->getLetterInstanceIssue() === 10))
            ->once()
            ->andReturn($this->okResponse(['id' => ['letterInstanceTodo' => 99], 'messages' => []]));

        $result = $this->postJson($sut, 'addCustomTodo', ['issueId' => 10]);

        $this->assertTrue($result['json']['success']);
        $this->assertSame(99, $result['json']['todoId']);
    }

    public function testDeleteCustomTodoSendsTheCommand(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('handleCommand')
            ->with(m::on(fn($cmd) => $cmd instanceof DeleteCustom && (int) $cmd->getId() === 99))
            ->once()
            ->andReturn($this->okResponse(['messages' => []]));

        $this->assertTrue($this->postJson($sut, 'deleteCustomTodo', ['todoId' => 99])['json']['success']);
    }

    /**
     * Validation messages come back nested by field. A plain implode turned them into
     * "Failed to save: Array".
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('saveActionProvider')]
    public function testARefusedSaveSaysWhyEvenWhenTheMessagesAreNested(string $action, array $body): void
    {
        $refused = m::mock(Response::class);
        $refused->shouldReceive('isOk')->andReturn(false);
        $refused->shouldReceive('getResult')->andReturn([
            'messages' => ['editedHeading' => ['stringLengthTooLong' => 'The heading is too long']],
        ]);
        $sut = $this->bareSut();
        $sut->shouldReceive('handleCommand')->once()->andReturn($refused);

        $result = $this->postJson($sut, $action, $body);

        $this->assertFalse($result['json']['success']);
        $this->assertSame('Failed to save: The heading is too long', $result['json']['message']);
    }

    /**
     * With no reason from the API there is nothing to put after the colon, so it isn't shown
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('saveActionProvider')]
    public function testARefusedSaveWithNoReasonGivenStillReadsProperly(string $action, array $body): void
    {
        $refused = m::mock(Response::class);
        $refused->shouldReceive('isOk')->andReturn(false);
        $refused->shouldReceive('getResult')->andReturn(['messages' => []]);
        $sut = $this->bareSut();
        $sut->shouldReceive('handleCommand')->once()->andReturn($refused);

        $this->assertSame('Failed to save', $this->postJson($sut, $action, $body)['json']['message']);
    }

    public static function saveActionProvider(): array
    {
        return [
            'issue' => ['saveIssueContent', ['issueId' => 10, 'editedContent' => ['blocks' => []], 'version' => 1]],
            'appendix' => ['saveAppendixContent', ['appendixId' => 10, 'editedContent' => ['blocks' => []], 'version' => 1]],
            'to-do' => ['saveTodoContent', ['todoId' => 10, 'editedDescription' => ['blocks' => []], 'version' => 1]],
            'section' => ['saveSectionContent', ['sectionId' => 10, 'editedContent' => ['blocks' => []], 'version' => 1]],
        ];
    }

    /**
     * PR review: these endpoints read the raw body, which Common\Module's CSRF check never sees.
     * Only a JSON request is accepted; another site can't send one without a CORS preflight.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('jsonActionProvider')]
    public function testJsonActionsRejectANonJsonPost(string $action, array $body): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('handleCommand')->never();

        $result = $this->postJson($sut, $action, $body, 'POST', 'text/plain');

        $this->assertSame(415, $result['status']);
        $this->assertFalse($result['json']['success']);
    }

    public static function jsonActionProvider(): array
    {
        return [
            'save issue' => ['saveIssueContent', ['issueId' => 10, 'editedContent' => [], 'version' => 1]],
            'save appendix' => ['saveAppendixContent', ['appendixId' => 10, 'editedContent' => [], 'version' => 1]],
            'save to-do' => ['saveTodoContent', ['todoId' => 10, 'editedDescription' => [], 'version' => 1]],
            'save section' => ['saveSectionContent', ['sectionId' => 10, 'editedContent' => [], 'version' => 1]],
            'add to-do' => ['addCustomTodo', ['issueId' => 10]],
            'delete to-do' => ['deleteCustomTodo', ['todoId' => 10]],
            'prepare to send' => ['prepareToSend', ['letterInstanceId' => 1, 'docTemplate' => 2]],
        ];
    }

    public function testCustomTodoActionsRejectGet(): void
    {
        $this->assertSame(405, $this->postJson($this->bareSut(), 'addCustomTodo', [], 'GET')['status']);
        $this->assertSame(405, $this->postJson($this->bareSut(), 'deleteCustomTodo', [], 'GET')['status']);
    }

    /**
     * Final review: an Other issue's to-do listed away from its issue keeps the issue's name.
     */
    public function testUnplacedCustomTodoIsNamedWithItsIssue(): void
    {
        $todos = [['id' => 3, 'isCustom' => true, 'name' => 'To-do 1', 'customLabel' => 'Vehicle storage: to-do 1', 'placementIssueIds' => [30]]];

        [, $unplaced] = $this->invokeProtected('placeTodosUnderIssues', [], $todos);

        $this->assertSame('Vehicle storage: to-do 1', $unplaced[0]['name']);
    }

    public function testSidebarPrefersTheIssuesDisplayName(): void
    {
        $issues = $this->invokeProtected('buildIssuesList', ['letterInstanceIssues' => [
            ['id' => 10, 'effectiveHeading' => 'Other issue', 'displayName' => 'Other issue (Finances)'],
        ]]);
        $todos = $this->invokeProtected('buildTodosList', ['letterInstanceTodos' => [
            ['id' => 20, 'isCustom' => true, 'customIndex' => 1, 'customLabel' => 'Other issue (Finances): to-do 1', 'letterInstanceIssue' => ['id' => 10]],
        ]]);

        $this->assertSame('Other issue (Finances)', $issues[0]['name']);
        $this->assertSame('Other issue (Finances): to-do 1', $todos[0]['name']);
    }

    /**
     * Intro, issues, closing and interim sections, in letter order. Interim wasn't ticked, so the
     * API marks its section as having nothing to edit. The issues come back Finance, Adverts,
     * Finance; one to-do has none of its issues on the letter.
     */
    private function letterWithSectionsIssuesAndTodos(): array
    {
        $section = static fn (int $id, string $key, string $name, array $extra = []): array => $extra + [
            'id' => $id,
            'version' => 1,
            'letterSectionVersion' => ['name' => $name, 'letterSectionVariant' => ['letterSection' => ['sectionKey' => $key]]],
        ];
        $issue = static fn (int $id, int $typeId, string $typeName, string $heading): array => [
            'id' => $id,
            'version' => 1,
            'effectiveHeading' => $heading,
            'letterIssueVersion' => ['heading' => $heading, 'letterIssueType' => ['id' => $typeId, 'name' => $typeName]],
        ];
        $todo = static fn (int $id, string $key, array $placementIssueIds): array => [
            'id' => $id,
            'version' => 1,
            'placementIssueIds' => $placementIssueIds,
            'letterTodoVersion' => ['name' => 'Send it to us', 'letterTodo' => ['todoKey' => $key]],
        ];

        return [
            'id' => 55,
            'isOpenForChanges' => true,
            'isSent' => false,
            'letterInstanceSections' => [
                $section(1, '001', 'Introductory wording'),
                $section(2, '__ISSUES__', 'Issues (Auto-generated)'),
                $section(3, '002', 'Closing wording'),
                $section(4, '003', 'Interim wording', ['nothingToEdit' => true]),
            ],
            'letterInstanceIssues' => [
                $issue(10, 2, 'Finance', 'Bank statements'),
                $issue(20, 1, 'Adverts', 'Advert wording'),
                $issue(11, 2, 'Finance', 'Overdraft'),
            ],
            'letterInstanceTodos' => [
                $todo(102, 'AD01', [20]),
                $todo(100, 'FI01', [11, 10]),
                $todo(101, 'FI02', []),
            ],
            'letterInstanceAppendices' => [
                ['id' => 7, 'version' => 1, 'letterAppendixVersion' => ['name' => 'Financial evidence', 'appendixType' => 'editable']],
                ['id' => 8, 'version' => 1, 'letterAppendixVersion' => ['name' => 'Application form', 'appendixType' => 'pdf']],
            ],
        ];
    }

    /**
     * A controller that can run the preview and edit pages for $letter, given the query string
     */
    private function sutShowing(array $letter, array $query): Sut
    {
        $flash = m::mock(FlashMessengerHelperService::class);
        $flash->shouldReceive('clearCurrentMessagesFromContainer');
        $navigationPage = m::mock();
        $navigationPage->shouldReceive('setActive');
        $navigation = m::mock(Navigation::class);
        $navigation->shouldReceive('findOneBy')->andReturn($navigationPage);

        $sut = m::mock(Sut::class, [
            m::mock(TranslationHelperService::class),
            m::mock(FormHelperService::class),
            $flash,
            $navigation,
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $params = m::mock(\Laminas\Mvc\Controller\Plugin\Params::class);
        $params->shouldReceive('fromQuery')
            ->andReturnUsing(static fn (string $name, mixed $default = null): mixed => $query[$name] ?? $default);
        $placeholder = m::mock(Placeholder::class);
        $placeholder->shouldReceive('setPlaceholder');
        $viewBuilder = m::mock(ViewBuilder::class);
        $viewBuilder->shouldReceive('buildView')->andReturnUsing(static fn (ViewModel $view): ViewModel => $view);

        $sut->shouldReceive('params')->andReturn($params);
        $sut->shouldReceive('placeholder')->andReturn($placeholder);
        $sut->shouldReceive('viewBuilder')->andReturn($viewBuilder);
        $sut->shouldReceive('handleQuery')->andReturn($this->okResponse($letter));
        $sut->shouldReceive('fetchLetterInstanceById')->andReturn($letter);
        $sut->shouldReceive('checkRequiredSections')->andReturn([]);

        return $sut;
    }

    private function renderLetterTemplate(string $template, ViewModel $view): \DOMXPath
    {
        $renderer = new PhpRenderer();
        $renderer->setResolver(new TemplatePathStack([
            'script_paths' => [__DIR__ . '/../../../../../module/Olcs/view'],
        ]));

        $page = new \DOMDocument();
        $page->loadHTML($renderer->render($template, $view->getVariables()), LIBXML_NOERROR);

        return new \DOMXPath($page);
    }

    /**
     * The preview sidebar's tick boxes, top to bottom, as "kind id"
     *
     * @return list<string>
     */
    private function sidebarOrder(array $letter): array
    {
        $sut = $this->sutShowing($letter, ['id' => '55']);
        $sut->previewAction();
        $sidebar = $this->renderLetterTemplate('pages/letter/preview-sidebar', $sut->getLeftView());

        $kinds = ['letterSections[]' => 'section', 'sections[]' => 'issue', 'todos[]' => 'todo', 'appendices[]' => 'appendix'];
        $order = [];
        foreach ($sidebar->query('//input[@type="checkbox"]') as $box) {
            $order[] = $kinds[$box->getAttribute('name')] . ' ' . $box->getAttribute('value');
        }

        return $order;
    }

    /**
     * The edit page's editors, top to bottom, as "kind id", with everything in $ticked chosen
     *
     * @param list<string> $ticked "kind id", as sidebarOrder() gives them
     * @return list<string>
     */
    private function editorOrder(array $letter, array $ticked): array
    {
        $idsOf = static fn (string $kind): array => array_values(array_filter(array_map(
            static fn (string $item): ?string => str_starts_with($item, $kind . ' ') ? substr($item, strlen($kind) + 1) : null,
            $ticked
        )));

        $view = $this->sutShowing($letter, [
            'id' => '55',
            'letterSections' => $idsOf('section'),
            'sections' => $idsOf('issue'),
            'todos' => $idsOf('todo'),
            'appendices' => $idsOf('appendix'),
        ])->editAction();
        $editPage = $this->renderLetterTemplate('pages/letter/edit', $view);

        $order = [];
        foreach ($editPage->query('//div[contains(@class, "-editor-group")]') as $editor) {
            foreach (['section', 'issue', 'todo', 'appendix'] as $kind) {
                if ($editor->hasAttribute('data-' . $kind . '-id')) {
                    $order[] = $kind . ' ' . $editor->getAttribute('data-' . $kind . '-id');
                }
            }
        }

        return $order;
    }

    /**
     * The sidebar lists what can be edited in the order the edit page shows its editors: sections
     * where they sit in the letter, issues kept together by type, each to-do under its issue,
     * then the appendices.
     */
    public function testPreviewSidebarIsInTheOrderOfTheEditPage(): void
    {
        $letter = $this->letterWithSectionsIssuesAndTodos();

        $sidebar = $this->sidebarOrder($letter);

        $this->assertSame(
            ['section 1', 'issue 10', 'issue 11', 'todo 100', 'issue 20', 'todo 102', 'todo 101', 'section 3', 'appendix 7'],
            $sidebar
        );
        $this->assertSame($sidebar, $this->editorOrder($letter, $sidebar));
    }

    /**
     * The interim paragraph is only in the letter when Interim was ticked. Without it the letter
     * still carries the section, with no wording, and the sidebar shouldn't offer it for editing.
     */
    public function testPreviewSidebarLeavesOutASectionWithNothingToEdit(): void
    {
        $letter = $this->letterWithSectionsIssuesAndTodos();

        $this->assertNotContains('section 4', $this->sidebarOrder($letter));

        $letter['letterInstanceSections'][3]['nothingToEdit'] = false;
        $this->assertContains('section 4', $this->sidebarOrder($letter));
    }

    /**
     * The preview sidebar and the edit page name a section the same way, and neither treats the
     * issues placeholder as a section to edit
     */
    public function testASectionIsNamedTheSameOnThePreviewAndEditPages(): void
    {
        $named = fn (array $version): ?string => $this->invokeProtected('instanceSectionName', ['id' => 9, 'letterSectionVersion' => $version]);

        $this->assertSame('Closing wording', $named(['name' => 'Closing wording', 'letterSectionVariant' => ['letterSection' => ['sectionKey' => '002']]]));
        $this->assertSame('Closing', $named(['letterSectionVariant' => ['letterSection' => ['name' => 'Closing', 'sectionKey' => '002']]]));
        $this->assertSame('002', $named(['letterSection' => ['sectionKey' => '002']]));
        $this->assertSame('Section #9', $named([]));
        $this->assertNull($named(['name' => 'Issues (Auto-generated)', 'letterSectionVariant' => ['letterSection' => ['sectionKey' => '__ISSUES__']]]));
    }

    public function testPreviewSidebarStillFlagsASectionThatNeedsInput(): void
    {
        $sections = $this->invokeProtected('buildInstanceSectionsList', ['letterInstanceSections' => [
            ['id' => 5, 'letterSectionVersion' => ['name' => 'Your reasons', 'requiresInput' => true]],
            ['id' => 6, 'editedContent' => ['blocks' => []], 'letterSectionVersion' => ['name' => 'Your reasons', 'requiresInput' => true]],
            $this->instanceSection(7, '__ISSUES__'),
        ]]);

        $this->assertSame([5, 6], array_column($sections, 'id'));
        $this->assertSame(['Your reasons', 'Your reasons'], array_column($sections, 'name'));
        $this->assertSame([true, false], array_column($sections, 'inputPending'));
    }

    /**
     * The edit page has one button that saves every editor with unsaved changes, and somewhere
     * to say they're all saved (letter-edit.js drives both)
     */
    public function testEditPageOffersToSaveEverythingAtOnce(): void
    {
        $letter = $this->letterWithSectionsIssuesAndTodos();

        $view = $this->sutShowing($letter, ['id' => '55', 'sections' => ['10'], 'letterSections' => ['1']])->editAction();
        $editPage = $this->renderLetterTemplate('pages/letter/edit', $view);

        $this->assertSame(1, $editPage->query('//button[@id="save-all-btn"]')->length);
        $this->assertSame(1, $editPage->query('//*[@id="save-all-indicator"]')->length);
    }

    /**
     * After a letter is sent the caseworker goes back to the documents of whatever the send form
     * was opened on. A letter raised on an application also carries the licence, so the form has
     * to open on the application, not the licence. Nothing else moves: a letter raised on a case
     * is still sent from the licence, because the send form can't yet propose to revoke from a
     * case's own documents.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('sendFormRoutes')]
    public function testSendFormOpensWhereTheLetterWasRaised(array $letter, string $route, array $routeParams): void
    {
        // every route asked for is recorded: the controller moves on to the next one if a route
        // can't be built, so a mock that only accepted the right route would hide a wrong first try
        $asked = [];
        $url = m::mock(\Laminas\Mvc\Controller\Plugin\Url::class);
        $url->shouldReceive('fromRoute')->andReturnUsing(
            static function (string $route, array $params) use (&$asked): string {
                $asked[] = [$route, $params];

                return '/the-send-form';
            }
        );
        $sut = $this->bareSut();
        $sut->shouldReceive('url')->andReturn($url);

        $this->assertSame('/the-send-form', $this->invokeProtectedOn($sut, 'buildPrintActionUrl', $letter, 99));
        $this->assertSame([[$route, $routeParams + ['doc' => 99, 'action' => 'print']]], $asked);
    }

    public static function sendFormRoutes(): \Iterator
    {
        yield 'raised on an application' => [
            ['licence' => ['id' => 7], 'application' => ['id' => 8], 'case' => null],
            'lva-application/documents/finalise',
            ['application' => 8],
        ];
        yield 'raised on a licence' => [
            ['licence' => ['id' => 7], 'application' => null, 'case' => null],
            'licence/documents/finalise',
            ['licence' => 7],
        ];
        yield 'raised on a case' => [
            ['licence' => ['id' => 7], 'application' => null, 'case' => ['id' => 24]],
            'licence/documents/finalise',
            ['licence' => 7],
        ];
        // a case with no licence of its own gives the letter its application as well
        yield 'raised on a case that is on an application' => [
            ['licence' => ['id' => 7], 'application' => ['id' => 8], 'case' => ['id' => 24]],
            'licence/documents/finalise',
            ['licence' => 7],
        ];
    }

    private function invokeProtectedOn(Sut $sut, string $method, mixed ...$args): mixed
    {
        return (new \ReflectionMethod(Sut::class, $method))->invoke($sut, ...$args);
    }
}
