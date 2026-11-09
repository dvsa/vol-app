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
use Dvsa\Olcs\Transfer\Query\Letter\LetterIssue\GetList as LetterIssueList;
use Dvsa\Olcs\Transfer\Query\Letter\LetterIssueType\GetList as LetterIssueTypeList;
use Laminas\Http\Request;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
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

    private function runCreateAction(Sut $sut, array $query): void
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

        $sut->createAction();
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

    private function letterForChangeIssues(array $overrides = []): array
    {
        return $overrides + [
            'id' => 55,
            'status' => ['id' => 'ltr_sts_draft'],
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

    public function testChangeIssuesOnASentLetterIsLocked(): void
    {
        $sut = $this->bareSut();
        $sut->shouldReceive('fetchLetterInstanceById')->andReturn(
            $this->letterForChangeIssues(['status' => ['id' => 'ltr_sts_sent']])
        );
        $sut->shouldReceive('buildAccordionData')->never();

        $view = $this->runChangeIssues($sut, ['id' => '55']);

        $this->assertTrue($view->getVariable('locked'));
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
                ['id' => 20, 'isCustom' => true, 'customIndex' => 2, 'inputPending' => true, 'letterInstanceIssue' => ['id' => 10]],
                ['id' => 21, 'isCustom' => false, 'letterTodoVersion' => ['name' => 'Send a lease', 'letterTodo' => ['todoKey' => 'FI02']]],
            ],
        ];

        $todos = $this->invokeProtected('buildTodosList', $data);

        $this->assertSame(['Vehicle storage: to-do 2', 'Send a lease (FI02)'], array_column($todos, 'name'));
        $this->assertSame([true, false], array_column($todos, 'inputPending'));
        $this->assertSame('To-do 2', $this->invokeProtected('todoEditorName', $data['letterInstanceTodos'][0]));
        $this->assertSame('Send a lease (FI02)', $this->invokeProtected('todoEditorName', $data['letterInstanceTodos'][1]));
    }

    private function postJson(Sut $sut, string $action, array $body, string $method = 'POST'): array
    {
        $request = new Request();
        $request->setMethod($method);
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
}
