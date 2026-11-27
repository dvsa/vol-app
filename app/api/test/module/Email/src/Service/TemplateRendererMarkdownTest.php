<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Service;

use Dvsa\Olcs\Api\Service\Template\StrategySelectingViewRenderer;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class TemplateRendererMarkdownTest extends MockeryTestCase
{
    public function testRenderMarkdownBodyDispatchesToMdFormat(): void
    {
        $renderer = m::mock(StrategySelectingViewRenderer::class);
        $renderer->shouldReceive('render')
            ->once()
            ->with('en_GB', 'md', 'notify-smoke-plain', ['name' => 'world'])
            ->andReturn('Hello **world**');

        $sut = new TemplateRenderer()->setViewRenderer($renderer);
        $message = new Message('user@example.com', 'Subj')->setLocale('en_GB');

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

        $sut = new TemplateRenderer()->setViewRenderer($renderer);
        $message = new Message('user@example.com', 'Subj')->setLocale('cy_GB');

        $sut->renderMarkdownBody($message, 'notify-smoke-cy');

        $this->assertSame('Helo', $message->getMarkdownBody());
    }

    public function testRenderMarkdownBodyAddsLicenceLineWhenBodyLacksLicNo(): void
    {
        $sut = $this->sutRendering('Hello');
        $message = new Message('user@example.com', 'Subj')->setLocale('en_GB');

        $sut->renderMarkdownBody($message, 'tpl', ['licNo' => 'OB1234567']);

        $this->assertSame("Licence: OB1234567\n\nHello", $message->getMarkdownBody());
    }

    public function testRenderMarkdownBodyUsesWelshLicenceLabel(): void
    {
        $sut = $this->sutRendering('Helo');
        $message = new Message('user@example.com', 'Subj')->setLocale('cy_GB');

        $sut->renderMarkdownBody($message, 'tpl', ['licNo' => 'OB1234567']);

        $this->assertSame("Trwydded: OB1234567\n\nHelo", $message->getMarkdownBody());
    }

    public function testRenderMarkdownBodyDoesNotAddLicenceLineWhenBodyHasLicNo(): void
    {
        $sut = $this->sutRendering('Your licence OB1234567 is ready');
        $message = new Message('user@example.com', 'Subj')->setLocale('en_GB');

        $sut->renderMarkdownBody($message, 'tpl', ['licNo' => 'OB1234567']);

        $this->assertSame('Your licence OB1234567 is ready', $message->getMarkdownBody());
    }

    public function testRenderMarkdownBodyLeavesBodyAloneWithoutLicNo(): void
    {
        $sut = $this->sutRendering('Hello');
        $message = new Message('user@example.com', 'Subj')->setLocale('en_GB');

        $sut->renderMarkdownBody($message, 'tpl', ['licNo' => '']);

        $this->assertSame('Hello', $message->getMarkdownBody());
    }

    private function sutRendering(string $markdown): TemplateRenderer
    {
        $renderer = m::mock(StrategySelectingViewRenderer::class);
        $renderer->shouldReceive('render')->once()->andReturn($markdown);

        return new TemplateRenderer()->setViewRenderer($renderer);
    }

    public function testRenderBodyInNotifyModeRoutesThroughMarkdownAndStampsTemplateKey(): void
    {
        $renderer = m::mock(StrategySelectingViewRenderer::class);
        $renderer->shouldReceive('render')
            ->once()
            ->with('en_GB', 'md', 'my-template', ['foo' => 'bar'])
            ->andReturn('Hello world');
        // Legacy html/plain renders must NOT happen in Notify mode.
        $renderer->shouldNotReceive('render')->with(m::any(), 'html', m::any(), m::any());
        $renderer->shouldNotReceive('render')->with(m::any(), 'plain', m::any(), m::any());

        $sut = new TemplateRenderer()
            ->setViewRenderer($renderer)
            ->setNotifyMode(true)
            ->setPassthroughTemplateUuids(['en_GB' => 'uuid-en', 'cy_GB' => 'uuid-cy']);

        $message = new Message('user@example.com', 'Subj')->setLocale('en_GB');

        $sut->renderBody($message, 'my-template', ['foo' => 'bar']);

        $this->assertSame('Hello world', $message->getMarkdownBody());
        $this->assertSame('uuid-en', $message->getTemplateKey());
        $this->assertNull($message->getPlainBody());
        $this->assertNull($message->getHtmlBody());
    }

    public function testRenderBodyInNotifyModeAddsLicenceLine(): void
    {
        $renderer = m::mock(StrategySelectingViewRenderer::class);
        $renderer->shouldReceive('render')
            ->once()
            ->with('en_GB', 'md', 'my-template', ['licNo' => 'OB1234567'])
            ->andReturn('Hello world');

        $sut = new TemplateRenderer()
            ->setViewRenderer($renderer)
            ->setNotifyMode(true)
            ->setPassthroughTemplateUuids(['en_GB' => 'uuid-en', 'cy_GB' => 'uuid-cy']);

        $message = new Message('user@example.com', 'Subj')->setLocale('en_GB');

        $sut->renderBody($message, 'my-template', ['licNo' => 'OB1234567']);

        $this->assertSame("Licence: OB1234567\n\nHello world", $message->getMarkdownBody());
        $this->assertSame('uuid-en', $message->getTemplateKey());
    }

    public function testRenderBodyInSmtpModeUsesLegacyHtmlPlainPath(): void
    {
        $renderer = m::mock(StrategySelectingViewRenderer::class);
        $renderer->shouldReceive('render')
            ->once()
            ->with('en_GB', 'plain', 'default', m::on(fn($args) => is_array($args) && ($args['content'] ?? '') === 'plain content'))
            ->andReturn('wrapped plain');
        $renderer->shouldReceive('render')
            ->once()
            ->with('en_GB', 'plain', 'my-template', ['foo' => 'bar'])
            ->andReturn('plain content');
        $renderer->shouldReceive('render')
            ->once()
            ->with('en_GB', 'html', 'default', m::on(fn($args) => is_array($args) && ($args['content'] ?? '') === 'html content'))
            ->andReturn('wrapped html');
        $renderer->shouldReceive('render')
            ->once()
            ->with('en_GB', 'html', 'my-template', ['foo' => 'bar'])
            ->andReturn('html content');

        $sut = new TemplateRenderer()->setViewRenderer($renderer);

        $message = new Message('user@example.com', 'Subj')->setLocale('en_GB');

        $sut->renderBody($message, 'my-template', ['foo' => 'bar']);

        $this->assertSame('wrapped plain', $message->getPlainBody());
        $this->assertSame('wrapped html', $message->getHtmlBody());
        $this->assertNull($message->getMarkdownBody());
        $this->assertNull($message->getTemplateKey());
    }

    public function testGetPassthroughTemplateUuidReturnsNullForEmptyOrMissing(): void
    {
        $sut = new TemplateRenderer()->setPassthroughTemplateUuids(['en_GB' => '', 'cy_GB' => 'uuid-cy']);

        $this->assertNull($sut->getPassthroughTemplateUuid('en_GB'));
        $this->assertSame('uuid-cy', $sut->getPassthroughTemplateUuid('cy_GB'));
        $this->assertNull($sut->getPassthroughTemplateUuid('fr_FR'));
    }
}
