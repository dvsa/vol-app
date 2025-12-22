<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection;
use Dvsa\Olcs\Api\Service\EditorJs\ConverterService;
use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\ContentSectionRenderer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Api\Service\Letter\SectionRenderer\ContentSectionRenderer
 */
class ContentSectionRendererTest extends MockeryTestCase
{
    private ContentSectionRenderer $sut;
    private m\MockInterface|ConverterService $mockConverterService;

    public function setUp(): void
    {
        $this->mockConverterService = m::mock(ConverterService::class);
        $this->sut = new ContentSectionRenderer($this->mockConverterService);
    }

    public function testRenderWithContent(): void
    {
        $content = [
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Test content']],
            ],
        ];

        $mockSection = m::mock(LetterInstanceSection::class);
        $mockSection->shouldReceive('getEffectiveContent')
            ->andReturn($content);

        $this->mockConverterService->shouldReceive('convertJsonToHtml')
            ->with(json_encode($content))
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
}
