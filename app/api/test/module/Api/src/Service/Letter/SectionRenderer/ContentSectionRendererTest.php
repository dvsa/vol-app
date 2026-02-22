<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection;
use Dvsa\Olcs\Api\Service\EditorJs\ConverterService;
use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\ContentSectionRenderer;
use Dvsa\Olcs\Api\Service\Letter\VolGrabReplacementService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Letter\SectionRenderer\ContentSectionRenderer::class)]
class ContentSectionRendererTest extends MockeryTestCase
{
    private ContentSectionRenderer $sut;
    private m\MockInterface|ConverterService $mockConverterService;
    private m\MockInterface|VolGrabReplacementService $mockVolGrabService;

    public function setUp(): void
    {
        $this->mockConverterService = m::mock(ConverterService::class);
        $this->mockVolGrabService = m::mock(VolGrabReplacementService::class);
        $this->sut = new ContentSectionRenderer($this->mockConverterService, $this->mockVolGrabService);
    }

    public function testRenderWithContent(): void
    {
        $content = [
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Test content']],
            ],
        ];
        $jsonContent = json_encode($content);

        $mockSection = m::mock(LetterInstanceSection::class);
        $mockSection->shouldReceive('getEffectiveContent')
            ->andReturn($content);

        $this->mockVolGrabService->shouldReceive('replaceGrabs')
            ->with($jsonContent, [])
            ->andReturn($jsonContent);

        $this->mockConverterService->shouldReceive('convertJsonToHtml')
            ->with($jsonContent)
            ->andReturn('<p>Test content</p>');

        $result = $this->sut->render($mockSection);

        $this->assertEquals('<div class="section"><p>Test content</p></div>', $result);
    }

    public function testRenderWithEmptyContent(): void
    {
        $mockSection = m::mock(LetterInstanceSection::class);
        $mockSection->shouldReceive('getEffectiveContent')
            ->andReturn([]);

        $result = $this->sut->render($mockSection);

        $this->assertEquals('', $result);
    }

    public function testRenderWithNullContent(): void
    {
        $mockSection = m::mock(LetterInstanceSection::class);
        $mockSection->shouldReceive('getEffectiveContent')
            ->andReturn(null);

        $result = $this->sut->render($mockSection);

        $this->assertEquals('', $result);
    }

    public function testRenderThrowsExceptionForUnsupportedEntity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ContentSectionRenderer only supports LetterInstanceSection entities');

        $unsupportedEntity = new \stdClass();
        $this->sut->render($unsupportedEntity);
    }

    public function testSupportsLetterInstanceSection(): void
    {
        $mockSection = m::mock(LetterInstanceSection::class);

        $this->assertTrue($this->sut->supports($mockSection));
    }

    public function testSupportsReturnsFalseForOtherObjects(): void
    {
        $otherObject = new \stdClass();

        $this->assertFalse($this->sut->supports($otherObject));
    }

    public function testRenderCallsVolGrabReplacement(): void
    {
        $content = [
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Hello [[OP_NAME]]']],
            ],
        ];
        $jsonContent = json_encode($content);
        $context = ['licence' => 123];

        $replacedContent = [
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Hello Test Operator']],
            ],
        ];
        $replacedJson = json_encode($replacedContent);

        $mockSection = m::mock(LetterInstanceSection::class);
        $mockSection->shouldReceive('getEffectiveContent')
            ->andReturn($content);

        $this->mockVolGrabService->shouldReceive('replaceGrabs')
            ->with($jsonContent, $context)
            ->once()
            ->andReturn($replacedJson);

        $this->mockConverterService->shouldReceive('convertJsonToHtml')
            ->with($replacedJson)
            ->andReturn('<p>Hello Test Operator</p>');

        $result = $this->sut->render($mockSection, $context);

        $this->assertEquals('<div class="section"><p>Hello Test Operator</p></div>', $result);
    }

    public function testRenderWithEmptyContextPassedToVolGrabs(): void
    {
        $content = [
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Some content']],
            ],
        ];
        $jsonContent = json_encode($content);

        $mockSection = m::mock(LetterInstanceSection::class);
        $mockSection->shouldReceive('getEffectiveContent')
            ->andReturn($content);

        $this->mockVolGrabService->shouldReceive('replaceGrabs')
            ->with($jsonContent, [])
            ->once()
            ->andReturn($jsonContent);

        $this->mockConverterService->shouldReceive('convertJsonToHtml')
            ->with($jsonContent)
            ->andReturn('<p>Some content</p>');

        $result = $this->sut->render($mockSection, []);

        $this->assertEquals('<div class="section"><p>Some content</p></div>', $result);
    }
}
