<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Service\Template\StrategySelectingViewRenderer;
use Dvsa\Olcs\Email\Service\TemplateRenderer;

/**
 * TemplateRendererTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TemplateRendererTest extends MockeryTestCase
{
    /**
     *
     * @param bool $hasHtml
     * @param int $htmlRenderTimes
     * @param string|null $renderedHtml
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('renderBodyProvider')]
    public function testRenderBody(mixed $hasHtml, mixed $htmlRenderTimes, mixed $renderedHtml): void
    {
        $sut = new TemplateRenderer();
        $message = new \Dvsa\Olcs\Email\Data\Message('TO', 'SUBJECT');
        $message->setLocale('en_GB');
        $message->setHasHtml($hasHtml);

        $mockViewRenderer = m::mock(StrategySelectingViewRenderer::class);
        $this->assertSame($sut, $sut->setViewRenderer($mockViewRenderer));

        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'plain', 'TEMPLATE', ['var1', 'var2'])
            ->once()
            ->andReturn('RENDER_PLAIN_TEMPLATE');

        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'plain', 'default', ['content' => 'RENDER_PLAIN_TEMPLATE'])
            ->once()
            ->andReturn('RENDER_PLAIN_LAYOUT');

        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'html', 'TEMPLATE', ['var1', 'var2'])
            ->times($htmlRenderTimes)
            ->andReturn('RENDER_HTML_TEMPLATE');

        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'html', 'default', ['content' => 'RENDER_HTML_TEMPLATE'])
            ->times($htmlRenderTimes)
            ->andReturn('RENDER_HTML_LAYOUT');

        $sut->renderBody($message, 'TEMPLATE', ['var1', 'var2']);

        $this->assertSame('RENDER_PLAIN_LAYOUT', $message->getPlainBody());
        $this->assertSame($renderedHtml, $message->getHtmlBody());
    }

    /**
     * Data provider for testRenderBody
     *
     * @return array
     */
    public static function renderBodyProvider(): array
    {
        return [
            [true, 1, 'RENDER_HTML_LAYOUT'],
            [false, 0, null]
        ];
    }
}
