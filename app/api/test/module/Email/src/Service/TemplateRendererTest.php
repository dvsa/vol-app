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
final class TemplateRendererTest extends MockeryTestCase
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
            ->with('en_GB', 'plain', 'default', ['var1', 'var2', 'content' => 'RENDER_PLAIN_TEMPLATE'])
            ->once()
            ->andReturn('RENDER_PLAIN_LAYOUT');

        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'html', 'TEMPLATE', ['var1', 'var2'])
            ->times($htmlRenderTimes)
            ->andReturn('RENDER_HTML_TEMPLATE');

        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'html', 'default', ['var1', 'var2', 'content' => 'RENDER_HTML_TEMPLATE'])
            ->times($htmlRenderTimes)
            ->andReturn('RENDER_HTML_LAYOUT');

        $sut->renderBody($message, 'TEMPLATE', ['var1', 'var2']);

        $this->assertSame('RENDER_PLAIN_LAYOUT', $message->getPlainBody());
        $this->assertSame($renderedHtml, $message->getHtmlBody());
    }

    public function testRenderBodyPassesVariablesToLayoutWithoutOverwritingContent(): void
    {
        $sut = new TemplateRenderer();
        $message = new \Dvsa\Olcs\Email\Data\Message('TO', 'SUBJECT');
        $message->setLocale('en_GB');
        $message->setHasHtml(false);

        $mockViewRenderer = m::mock(StrategySelectingViewRenderer::class);
        $sut->setViewRenderer($mockViewRenderer);

        $variables = ['licNo' => 'OB1234567', 'content' => 'HANDLER_CONTENT'];

        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'plain', 'TEMPLATE', $variables)
            ->once()
            ->andReturn('RENDER_PLAIN_TEMPLATE');

        // the layout gets the handler variables, but the rendered body replaces the handler's "content"
        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'plain', 'default', ['licNo' => 'OB1234567', 'content' => 'RENDER_PLAIN_TEMPLATE'])
            ->once()
            ->andReturn('RENDER_PLAIN_LAYOUT');

        $sut->renderBody($message, 'TEMPLATE', $variables);

        $this->assertSame('RENDER_PLAIN_LAYOUT', $message->getPlainBody());
    }

    public function testRenderBodyKeepsLicNoOutOfLayoutWhenBodyPrintsIt(): void
    {
        $sut = new TemplateRenderer();
        $message = new \Dvsa\Olcs\Email\Data\Message('TO', 'SUBJECT');
        $message->setLocale('en_GB');
        $message->setHasHtml(false);

        $mockViewRenderer = m::mock(StrategySelectingViewRenderer::class);
        $sut->setViewRenderer($mockViewRenderer);

        $variables = ['licNo' => 'OB1234567', 'operatorName' => 'OPERATOR'];

        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'plain', 'TEMPLATE', $variables)
            ->once()
            ->andReturn('Your licence OB1234567 is due');

        // the layout gets the handler variables without licNo, plus the rendered body as "content"
        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'plain', 'default', ['operatorName' => 'OPERATOR', 'content' => 'Your licence OB1234567 is due'])
            ->once()
            ->andReturn('RENDER_PLAIN_LAYOUT');

        $sut->renderBody($message, 'TEMPLATE', $variables);

        $this->assertSame('RENDER_PLAIN_LAYOUT', $message->getPlainBody());
    }

    public function testRenderBodyChecksLicNoSeparatelyForPlainAndHtmlBodies(): void
    {
        $sut = new TemplateRenderer();
        $message = new \Dvsa\Olcs\Email\Data\Message('TO', 'SUBJECT');
        $message->setLocale('en_GB');
        $message->setHasHtml(true);

        $mockViewRenderer = m::mock(StrategySelectingViewRenderer::class);
        $sut->setViewRenderer($mockViewRenderer);

        $variables = ['licNo' => 'OB1234567', 'operatorName' => 'OPERATOR'];

        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'plain', 'TEMPLATE', $variables)
            ->once()
            ->andReturn('RENDER_PLAIN_TEMPLATE');

        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'html', 'TEMPLATE', $variables)
            ->once()
            ->andReturn('<p>Your licence OB1234567 is due</p>');

        // the plain body does not print the licence number, so its layout keeps licNo
        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'plain', 'default', $variables + ['content' => 'RENDER_PLAIN_TEMPLATE'])
            ->once()
            ->andReturn('RENDER_PLAIN_LAYOUT');

        // the html body does, so its layout loses licNo
        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'html', 'default', ['operatorName' => 'OPERATOR', 'content' => '<p>Your licence OB1234567 is due</p>'])
            ->once()
            ->andReturn('RENDER_HTML_LAYOUT');

        $sut->renderBody($message, 'TEMPLATE', $variables);

        $this->assertSame('RENDER_PLAIN_LAYOUT', $message->getPlainBody());
        $this->assertSame('RENDER_HTML_LAYOUT', $message->getHtmlBody());
    }

    public function testRenderBodyGivesLicNoToLayoutWhenBodyDoesNotPrintIt(): void
    {
        $sut = new TemplateRenderer();
        $message = new \Dvsa\Olcs\Email\Data\Message('TO', 'SUBJECT');
        $message->setLocale('en_GB');
        $message->setHasHtml(false);

        $mockViewRenderer = m::mock(StrategySelectingViewRenderer::class);
        $sut->setViewRenderer($mockViewRenderer);

        $variables = ['licNo' => 'OB1234567', 'operatorName' => 'OPERATOR'];

        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'plain', 'TEMPLATE', $variables)
            ->once()
            ->andReturn('RENDER_PLAIN_TEMPLATE');

        // the layout gets the handler variables including licNo, plus the rendered body as "content"
        $mockViewRenderer->shouldReceive('render')
            ->with('en_GB', 'plain', 'default', $variables + ['content' => 'RENDER_PLAIN_TEMPLATE'])
            ->once()
            ->andReturn('RENDER_PLAIN_LAYOUT');

        $sut->renderBody($message, 'TEMPLATE', $variables);

        $this->assertSame('RENDER_PLAIN_LAYOUT', $message->getPlainBody());
    }

    /**
     * Data provider for testRenderBody
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function renderBodyProvider(): \Iterator
    {
        yield [true, 1, 'RENDER_HTML_LAYOUT'];
        yield [false, 0, null];
    }
}
