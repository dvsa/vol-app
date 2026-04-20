<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Service;

use Dvsa\Olcs\Api\Service\Template\StrategySelectingViewRenderer;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class TemplateRendererMarkdownTest extends MockeryTestCase
{
    public function testRenderMarkdownBodyDispatchesToMdFormat(): void
    {
        $renderer = m::mock(StrategySelectingViewRenderer::class);
        $renderer->shouldReceive('render')
            ->once()
            ->with('en_GB', 'md', 'notify-smoke-plain', ['name' => 'world'])
            ->andReturn('Hello **world**');

        $sut = (new TemplateRenderer())->setViewRenderer($renderer);
        $message = (new Message('user@example.com', 'Subj'))->setLocale('en_GB');

        $sut->renderMarkdownBody($message, 'notify-smoke-plain', ['name' => 'world']);

        $this->assertSame('Hello **world**', $message->getMarkdownBody());
        $this->assertNull($message->getPlainBody());
        $this->assertNull($message->getHtmlBody());
    }

    public function testRenderMarkdownBodyHonoursWelshLocale(): void
    {
        $renderer = m::mock(StrategySelectingViewRenderer::class);
        $renderer->shouldReceive('render')
            ->once()
            ->with('cy_GB', 'md', 'notify-smoke-cy', [])
            ->andReturn('Helo');

        $sut = (new TemplateRenderer())->setViewRenderer($renderer);
        $message = (new Message('user@example.com', 'Subj'))->setLocale('cy_GB');

        $sut->renderMarkdownBody($message, 'notify-smoke-cy');

        $this->assertSame('Helo', $message->getMarkdownBody());
    }
}
