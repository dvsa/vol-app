<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\Olcs\Api\Service\EditorJs\ConverterService;
use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\TodoSectionRenderer;
use Dvsa\Olcs\Api\Service\Letter\VolGrabReplacementService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Letter\SectionRenderer\TodoSectionRenderer::class)]
final class TodoSectionRendererTest extends MockeryTestCase
{
    private TodoSectionRenderer $sut;
    private m\MockInterface|ConverterService $mockConverterService;
    private m\MockInterface|VolGrabReplacementService $mockVolGrabService;

    #[\Override]
    public function setUp(): void
    {
        $this->mockConverterService = m::mock(ConverterService::class);
        // normalize() fills in the EditorJS envelope and returns conforming content
        // untouched, so the tests below assert against exactly what they pass in.
        $this->mockConverterService->shouldReceive('normalize')->andReturnUsing(fn(array $d): array => $d);
        $this->mockVolGrabService = m::mock(VolGrabReplacementService::class);
        $this->sut = new TodoSectionRenderer($this->mockConverterService, $this->mockVolGrabService);
    }

    /**
     * Real entities, not mocks. The renderer now reads one accessor, so a mocked to-do could be
     * told to return anything and the override-beats-standing precedence would never actually be
     * exercised -- the assertion would pass without the feature working.
     */
    private function todo(?array $standing, ?array $edited = null): LetterInstanceTodo
    {
        $version = new LetterTodoVersion();
        $version->setDescription($standing);

        $todo = new LetterInstanceTodo();
        $todo->setLetterTodoVersion($version);

        if ($edited !== null) {
            $todo->setEditedDescriptionFromArray($edited);
        }

        return $todo;
    }

    private function expectConversionTo(string $html): void
    {
        $this->mockVolGrabService->shouldReceive('replaceGrabs')
            ->andReturnUsing(fn($json, $context) => $json);
        $this->mockConverterService->shouldReceive('convertJsonToHtml')->andReturn($html);
    }

    public function testRenderOutputsBlockNotListItem(): void
    {
        // VOL-7280: a to-do rendered as <li> makes the whole to-do a bullet and
        // demotes bullets inside its own content to hollow second-level ones.
        $todo = $this->todo([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Upload bank statements']],
            ],
        ]);

        $this->expectConversionTo('<p>Upload bank statements</p><ul><li>in the name of the operator</li></ul>');

        $html = $this->sut->render($todo);

        $this->assertStringContainsString('<div class="todo-item">', $html);
        $this->assertStringNotContainsString('<li class="todo-item">', $html);
    }

    public function testRenderUsesTheCaseworkersEditWhenThereIsOne(): void
    {
        $standing = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Standing wording']]]];
        $edited = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Edited for this operator']]]];

        $todo = $this->todo($standing, $edited);

        $this->mockVolGrabService->shouldReceive('replaceGrabs')
            ->andReturnUsing(fn($json, $context) => $json);
        // Assert on what actually reached the converter: the edit, never the standing text.
        $this->mockConverterService->shouldReceive('convertJsonToHtml')
            ->once()
            ->with(m::on(fn(string $json): bool => str_contains($json, 'Edited for this operator')
                && !str_contains($json, 'Standing wording')))
            ->andReturn('<p>Edited for this operator</p>');

        $this->assertStringContainsString('Edited for this operator', $this->sut->render($todo));
    }

    public function testRenderLeavesTheSharedWordingUntouched(): void
    {
        // The safety property of the whole feature: an override is scoped to one letter.
        $standing = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Standing wording']]]];
        $todo = $this->todo($standing, ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Edited']]]]);

        $this->expectConversionTo('<p>Edited</p>');
        $this->sut->render($todo);

        $this->assertSame($standing, $todo->getLetterTodoVersion()->getDescription());
    }

    public function testRenderEmptyDescriptionReturnsEmptyString(): void
    {
        $this->assertSame('', $this->sut->render($this->todo(null)));
    }

    public function testRenderEmptyEditFallsBackToStandingWording(): void
    {
        // An edit cleared back to nothing must not blank the to-do.
        $todo = $this->todo(
            ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Standing wording']]]],
            []
        );

        $this->expectConversionTo('<p>Standing wording</p>');

        $this->assertStringContainsString('Standing wording', $this->sut->render($todo));
    }

    public function testRenderSurvivesADescriptionThatIsNotAnArray(): void
    {
        // Guards the regression the single-accessor refactor could have introduced: the renderer
        // used to check is_array() itself and return ''. If getEffectiveDescription() were not
        // total, a scalar here would be a TypeError rather than an empty render.
        $version = new LetterTodoVersion();
        $version->setDescription(5);

        $todo = new LetterInstanceTodo();
        $todo->setLetterTodoVersion($version);

        $this->assertSame('', $this->sut->render($todo));
    }
}
