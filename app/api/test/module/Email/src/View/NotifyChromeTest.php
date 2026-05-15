<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\View;

use Dvsa\Olcs\Email\View\NotifyChrome;
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
}
