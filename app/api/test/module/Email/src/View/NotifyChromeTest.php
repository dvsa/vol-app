<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\View;

use Dvsa\Olcs\Email\View\NotifyChrome;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PHPUnit\Framework\TestCase;

class NotifyChromeTest extends TestCase
{
    public function testTemplateContainsRequiredPlaceholders(): void
    {
        $template = NotifyChrome::template();

        $this->assertStringContainsString('{{subject}}', $template);
        $this->assertStringContainsString('{{body}}', $template);
    }

    public function testWrapSubstitutesBodyAndEscapesSubject(): void
    {
        $output = NotifyChrome::wrap('<p>Hello</p>', 'Reset your <password>');

        $this->assertStringContainsString('<p>Hello</p>', $output);
        $this->assertStringContainsString('Reset your &lt;password&gt;', $output);
        $this->assertStringNotContainsString('Reset your <password>', $output);
    }

    public function testWrapWithoutSubjectStillProducesValidHtml(): void
    {
        $output = NotifyChrome::wrap('<p>body</p>');

        $this->assertStringStartsWith('<!doctype html>', $output);
        $this->assertStringContainsString('<p>body</p>', $output);
        $this->assertStringNotContainsString('{{subject}}', $output);
        $this->assertStringNotContainsString('{{body}}', $output);
    }

    public function testWrapPreservesGovukWordmark(): void
    {
        $output = NotifyChrome::wrap('<p>hi</p>', 'subject');

        $this->assertStringContainsString('GOV.UK', $output);
        $this->assertStringContainsString('Sent via GOV.UK Notify', $output);
    }

    /**
     * Notify silently discards Markdown tables — the rows must not survive the preview either.
     */
    public function testRenderMarkdownBodyDropsTables(): void
    {
        $md = "Intro paragraph.\n\n| Field | Value |\n| --- | --- |\n| Licence | OB123 |\n| Centre | Leeds |\n\nOutro paragraph.";
        $html = NotifyChrome::renderMarkdownBody($md, new GithubFlavoredMarkdownConverter());

        $this->assertStringNotContainsString('<table', $html);
        $this->assertStringNotContainsString('Licence', $html);
        $this->assertStringNotContainsString('Leeds', $html);
        $this->assertStringContainsString('Intro paragraph.', $html);
        $this->assertStringContainsString('Outro paragraph.', $html);
    }

    /**
     * Notify has no bold/italic — the markers are shown literally, not rendered.
     */
    public function testRenderMarkdownBodyKeepsBoldAndItalicLiteral(): void
    {
        $html = NotifyChrome::renderMarkdownBody('This is **bold** and _italic_ text.', new GithubFlavoredMarkdownConverter());

        $this->assertStringNotContainsString('<strong>', $html);
        $this->assertStringNotContainsString('<em>', $html);
        $this->assertStringContainsString('**bold**', $html);
        $this->assertStringContainsString('_italic_', $html);
    }

    /**
     * Notify renders `#` as <h2> and `##` as <h3> (one level deeper than CommonMark).
     */
    public function testRenderMarkdownBodyShiftsHeadingLevels(): void
    {
        $html = NotifyChrome::renderMarkdownBody("# Heading\n\n## Subheading", new GithubFlavoredMarkdownConverter());

        $this->assertStringContainsString('<h2 style=', $html);
        $this->assertStringContainsString('<h3 style=', $html);
        $this->assertStringNotContainsString('<h1', $html);
    }

    /**
     * `^ text` becomes inset (call-out) text; lists carry Notify's inline styles.
     */
    public function testRenderMarkdownBodyRendersInsetAndStyledLists(): void
    {
        $html = NotifyChrome::renderMarkdownBody("^ Important note\n\n* one\n* two", new GithubFlavoredMarkdownConverter());

        $this->assertStringContainsString('<blockquote style="Margin:0 0 20px 0; border-left:10px solid #B1B4B6', $html);
        $this->assertStringContainsString('Important note', $html);
        $this->assertStringContainsString('<ul style=', $html);
        $this->assertStringContainsString('<li style=', $html);
    }
}
