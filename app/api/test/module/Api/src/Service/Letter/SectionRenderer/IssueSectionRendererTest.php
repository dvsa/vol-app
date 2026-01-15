<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection;
use Dvsa\Olcs\Api\Service\EditorJs\ConverterService;
use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\IssueSectionRenderer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Api\Service\Letter\SectionRenderer\IssueSectionRenderer
 */
class IssueSectionRendererTest extends MockeryTestCase
{
    private IssueSectionRenderer $sut;
    private m\MockInterface|ConverterService $mockConverterService;

    public function setUp(): void
    {
        $this->mockConverterService = m::mock(ConverterService::class);
        $this->sut = new IssueSectionRenderer($this->mockConverterService);
    }

    public function testRenderWithHeadingAndContent(): void
    {
        $heading = 'Test Issue Heading';
        $content = [
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Issue content here']],
            ],
        ];

        $mockIssue = m::mock(LetterInstanceIssue::class);
        $mockIssue->shouldReceive('getHeading')
            ->andReturn($heading);
        $mockIssue->shouldReceive('getEffectiveContent')
            ->andReturn($content);

        $this->mockConverterService->shouldReceive('convertJsonToHtml')
            ->with(json_encode($content))
            ->andReturn('<p>Issue content here</p>');

        $result = $this->sut->render($mockIssue);

        $expected = '<div class="issue">' .
            '<h4 class="issue-heading">' . htmlspecialchars($heading) . '</h4>' .
            '<div class="issue-body"><p>Issue content here</p></div>' .
            '</div>';

        $this->assertEquals($expected, $result);
    }

    public function testRenderWithHeadingOnly(): void
    {
        $heading = 'Test Issue Heading';

        $mockIssue = m::mock(LetterInstanceIssue::class);
        $mockIssue->shouldReceive('getHeading')
            ->andReturn($heading);
        $mockIssue->shouldReceive('getEffectiveContent')
            ->andReturn(null);

        $result = $this->sut->render($mockIssue);

        $expected = '<div class="issue">' .
            '<h4 class="issue-heading">' . htmlspecialchars($heading) . '</h4>' .
            '</div>';

        $this->assertEquals($expected, $result);
    }

    public function testRenderWithContentOnly(): void
    {
        $content = [
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Issue content here']],
            ],
        ];

        $mockIssue = m::mock(LetterInstanceIssue::class);
        $mockIssue->shouldReceive('getHeading')
            ->andReturn(null);
        $mockIssue->shouldReceive('getEffectiveContent')
            ->andReturn($content);

        $this->mockConverterService->shouldReceive('convertJsonToHtml')
            ->with(json_encode($content))
            ->andReturn('<p>Issue content here</p>');

        $result = $this->sut->render($mockIssue);

        $expected = '<div class="issue">' .
            '<div class="issue-body"><p>Issue content here</p></div>' .
            '</div>';

        $this->assertEquals($expected, $result);
    }

    public function testRenderWithEmptyHeadingAndContent(): void
    {
        $mockIssue = m::mock(LetterInstanceIssue::class);
        $mockIssue->shouldReceive('getHeading')
            ->andReturn('');
        $mockIssue->shouldReceive('getEffectiveContent')
            ->andReturn([]);

        $result = $this->sut->render($mockIssue);

        $expected = '<div class="issue"></div>';

        $this->assertEquals($expected, $result);
    }

    public function testRenderThrowsExceptionForUnsupportedEntity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('IssueSectionRenderer only supports LetterInstanceIssue entities');

        $unsupportedEntity = new \stdClass();
        $this->sut->render($unsupportedEntity);
    }

    public function testRenderThrowsExceptionForLetterInstanceSection(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('IssueSectionRenderer only supports LetterInstanceIssue entities');

        $mockSection = m::mock(LetterInstanceSection::class);
        $this->sut->render($mockSection);
    }

    public function testSupportsLetterInstanceIssue(): void
    {
        $mockIssue = m::mock(LetterInstanceIssue::class);

        $this->assertTrue($this->sut->supports($mockIssue));
    }

    public function testSupportsReturnsFalseForOtherObjects(): void
    {
        $otherObject = new \stdClass();

        $this->assertFalse($this->sut->supports($otherObject));
    }

    public function testSupportsReturnsFalseForLetterInstanceSection(): void
    {
        $mockSection = m::mock(LetterInstanceSection::class);

        $this->assertFalse($this->sut->supports($mockSection));
    }

    public function testRenderEscapesHtmlInHeading(): void
    {
        $heading = 'Test <script>alert("xss")</script> Heading';

        $mockIssue = m::mock(LetterInstanceIssue::class);
        $mockIssue->shouldReceive('getHeading')
            ->andReturn($heading);
        $mockIssue->shouldReceive('getEffectiveContent')
            ->andReturn(null);

        $result = $this->sut->render($mockIssue);

        // Verify XSS is escaped
        $this->assertStringContainsString('&lt;script&gt;', $result);
        $this->assertStringNotContainsString('<script>', $result);
    }
}
