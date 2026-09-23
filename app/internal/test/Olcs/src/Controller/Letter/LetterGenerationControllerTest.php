<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Letter;

use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
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
        $sut->shouldReceive('fetchLetterChoicesForLetterType')->andReturn([]);

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

        $this->runCreateAction($sut, ['template' => '5', 'licence' => '7']);
    }

    public function testCreateActionWithoutAnEntityShowsEverything(): void
    {
        $sut = $this->bareSut();

        $sut->shouldReceive('handleQuery')->never();
        $sut->shouldReceive('buildAccordionData')->with(null)->once()->andReturn([]);

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
}
