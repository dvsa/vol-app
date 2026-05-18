<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\View;

/**
 * Shared GOV.UK-alike chrome wrapper for Markdown-rendered email bodies.
 *
 * Used by:
 *   - {@see \Dvsa\Olcs\Email\Transport\DevNotifyTransport} when previewing into Mailpit locally
 *   - {@see \Dvsa\Olcs\Api\Domain\QueryHandler\Template\PreviewTemplateSource} when an admin
 *     hits the "Preview" button on a `format='md'` template
 *
 * Keeping a single source ensures both surfaces show admins the same approximation of what
 * Notify will render server-side at delivery time.
 */
final class NotifyChrome
{
    /**
     * Returns the chrome HTML template with `{{subject}}` and `{{body}}` placeholders.
     * Useful when the consumer wants to apply its own substitution (e.g. DevNotifyTransport
     * which goes through `strtr`).
     */
    public static function template(): string
    {
        return <<<'HTML'
            <!doctype html>
            <html lang="en">
            <head>
              <meta charset="utf-8">
              <title>{{subject}}</title>
            </head>
            <body style="margin:0; padding:0; background:#f3f2f1; font-family:'GDS Transport',Arial,Helvetica,sans-serif; color:#0b0c0c;">
              <div style="background:#0b0c0c; padding:10px 20px;">
                <span style="color:#ffffff; font-size:24px; font-weight:700; letter-spacing:1px;">GOV.UK</span>
              </div>
              <div style="max-width:620px; margin:0 auto; padding:20px;">
                <div style="background:#ffffff; padding:30px 30px 20px; border-top:10px solid #1d70b8;">
                  <h1 style="font-size:24px; line-height:1.25; margin:0 0 20px; color:#0b0c0c; font-weight:700;">{{subject}}</h1>
                  <div style="font-size:19px; line-height:1.47; color:#0b0c0c;">{{body}}</div>
                </div>
                <hr style="border:0; border-top:1px solid #b1b4b6; margin:30px 0 15px;">
                <p style="font-size:14px; color:#505a5f; margin:0;">
                  Sent via GOV.UK Notify (preview rendered locally).
                </p>
              </div>
            </body>
            </html>
            HTML;
    }

    /**
     * Wrap a rendered HTML body in the chrome template, substituting `{{subject}}` and `{{body}}`.
     * Subject is HTML-escaped; body is trusted (callers convert it from Markdown).
     */
    public static function wrap(string $bodyHtml, string $subject = ''): string
    {
        return strtr(self::template(), [
            '{{subject}}' => htmlspecialchars($subject, ENT_QUOTES, 'UTF-8'),
            '{{body}}' => $bodyHtml,
        ]);
    }
}
