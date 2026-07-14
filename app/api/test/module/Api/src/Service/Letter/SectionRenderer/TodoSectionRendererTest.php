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
class TodoSectionRendererTest extends MockeryTestCase
{
    private TodoSectionRenderer $sut;
    private m\MockInterface|ConverterService $mockConverterService;
    private m\MockInterface|VolGrabReplacementService $mockVolGrabService;

    public function setUp(): void
    {
        $this->mockConverterService = m::mock(ConverterService::class);
        $this->mockVolGrabService = m::mock(VolGrabReplacementService::class);
        $this->sut = new TodoSectionRenderer($this->mockConverterService, $this->mockVolGrabService);
    }

    public function testRenderOutputsBlockNotListItem(): void
    {
        // VOL-7280: a to-do rendered as <li> makes the whole to-do a bullet and
        // demotes bullets inside its own content to hollow second-level ones.
        $description = [
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Upload bank statements']],
            ],
        ];

        $mockVersion = m::mock(LetterTodoVersion::class);
        $mockVersion->shouldReceive('getDescription')->andReturn($description);

        $mockTodo = m::mock(LetterInstanceTodo::class);
        $mockTodo->shouldReceive('getLetterTodoVersion')->andReturn($mockVersion);

        $this->mockVolGrabService->shouldReceive('replaceGrabs')
            ->andReturnUsing(fn($json, $context) => $json);
        $this->mockConverterService->shouldReceive('convertJsonToHtml')
            ->andReturn('<p>Upload bank statements</p><ul><li>in the name of the operator</li></ul>');

        $html = $this->sut->render($mockTodo);

        $this->assertStringContainsString('<div class="todo-item">', $html);
        $this->assertStringNotContainsString('<li class="todo-item">', $html);
    }

    public function testRenderEmptyDescriptionReturnsEmptyString(): void
    {
        $mockVersion = m::mock(LetterTodoVersion::class);
        $mockVersion->shouldReceive('getDescription')->andReturn(null);

        $mockTodo = m::mock(LetterInstanceTodo::class);
        $mockTodo->shouldReceive('getLetterTodoVersion')->andReturn($mockVersion);

        $this->assertSame('', $this->sut->render($mockTodo));
    }
}
