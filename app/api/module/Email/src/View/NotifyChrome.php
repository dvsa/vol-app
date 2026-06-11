<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\View;

use League\CommonMark\ConverterInterface;

/**
 * Shared GOV.UK-alike chrome wrapper + Notify Markdown emulation for email previews.
 *
 * Used by:
 *   - {@see \Dvsa\Olcs\Email\Transport\DevNotifyTransport} when previewing into Mailpit locally
 *   - {@see \Dvsa\Olcs\Api\Domain\QueryHandler\Template\PreviewTemplateSource} when an admin
 *     hits the "Preview" button on a `format='md'` template
 *
 * {@see renderMarkdownBody()} deliberately mimics GOV.UK Notify's *restricted* Markdown rather
 * than full CommonMark/GFM, so the preview matches what recipients actually receive. The rules
 * below were reverse-engineered from a real delivered Notify email:
 *   - tables are silently DROPPED (Notify does not support them — the rows vanish entirely);
 *   - bold/italic are NOT supported — `**x**` / `_x_` are shown literally;
 *   - `^ text` becomes inset (call-out) text;
 *   - `#` renders as <h2> and `##` as <h3> (one level deeper than CommonMark);
 *   - every block element carries Notify's inline styles (captured from the real email).
 */
final class NotifyChrome
{
    /** Notify's inline styles, captured from a real delivered email, keyed by HTML tag. */
    private const STYLES = [
        'h2'         => 'Margin:0 0 15px 0; padding:10px 0 0 0; font-size:27px; line-height:35px; font-weight:bold; color:#0B0C0C;',
        'h3'         => 'Margin:0 0 15px 0; padding:10px 0 0 0; font-size:19px; line-height:25px; font-weight:bold; color:#0B0C0C;',
        'h4'         => 'Margin:0 0 15px 0; padding:10px 0 0 0; font-size:19px; line-height:25px; font-weight:bold; color:#0B0C0C;',
        'p'          => 'Margin:0 0 20px 0; font-size:19px; line-height:25px; color:#0B0C0C;',
        'ul'         => 'Margin:0 0 20px 20px; padding:0; list-style-type:disc;',
        'ol'         => 'Margin:0 0 20px 20px; padding:0; list-style-type:decimal;',
        'li'         => 'Margin:5px 0 5px; padding:0 0 0 5px; font-size:19px; line-height:25px; color:#0B0C0C;',
        'blockquote' => 'Margin:0 0 20px 0; border-left:10px solid #B1B4B6; padding:15px 0 0.1px 15px;',
    ];

    /**
     * Render a Markdown body the way GOV.UK Notify does, so local previews match delivery.
     */
    public static function renderMarkdownBody(string $markdown, ConverterInterface $converter): string
    {
        $html = $converter->convert(self::emulateNotifyMarkdown($markdown))->getContent();
        return self::applyNotifyStyles($html);
    }

    /**
     * Returns the chrome HTML template with `{{subject}}` and `{{body}}` placeholders.
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
              <div style="background:#1d70b8; padding:10px 20px;">
                <span style="color:#ffffff; font-size:24px; font-weight:700; letter-spacing:1px;">GOV.UK</span>
              </div>
              <div style="max-width:620px; margin:0 auto; padding:20px;">
                <div style="background:#ffffff; padding:30px 30px 20px; border-top:10px solid #1d70b8;">
                  <div style="font-size:19px; line-height:25px; color:#0b0c0c;">{{body}}</div>
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

    /**
     * Pre-process Markdown to match Notify's restricted feature set before it reaches CommonMark.
     */
    private static function emulateNotifyMarkdown(string $markdown): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $markdown) ?: [];
        $kept = [];
        foreach ($lines as $line) {
            // Notify discards Markdown tables entirely — drop any pipe-delimited row (and the
            // `| --- |` separator). The bare `---` horizontal rule has no leading pipe, so it stays.
            if (preg_match('/^\s*\|/', $line) === 1) {
                continue;
            }
            // Notify inset text: a line beginning with `^` becomes a call-out blockquote.
            if (preg_match('/^\s*\^\s?(.*)$/', $line, $m) === 1) {
                $kept[] = '> ' . $m[1];
                continue;
            }
            $kept[] = $line;
        }
        $markdown = implode("\n", $kept);

        // Notify has no bold/italic: keep the markers literal rather than emitting <strong>/<em>.
        $markdown = preg_replace_callback('/\*\*(.+?)\*\*/s', static fn(array $m): string => '\\*\\*' . $m[1] . '\\*\\*', $markdown) ?? $markdown;
        $markdown = preg_replace_callback('/(?<![\w\/:])_(?!_)([^_\n]+?)_(?![\w\/])/', static fn(array $m): string => '\\_' . $m[1] . '\\_', $markdown) ?? $markdown;

        return $markdown;
    }

    /**
     * Post-process CommonMark HTML: shift heading levels (Notify renders `#`->h2, `##`->h3) and
     * apply Notify's captured inline styles to every block element.
     */
    private static function applyNotifyStyles(string $html): string
    {
        $html = preg_replace_callback(
            '/<(\/?)h([1-3])\b([^>]*)>/i',
            static fn(array $m): string => '<' . $m[1] . 'h' . ((int) $m[2] + 1) . $m[3] . '>',
            $html
        ) ?? $html;

        foreach (self::STYLES as $tag => $style) {
            $html = preg_replace('/<' . $tag . '(\s*)>/i', '<' . $tag . ' style="' . $style . '">', $html) ?? $html;
        }

        $html = preg_replace('/<hr\s*\/?>/i', '<hr style="border:0; height:1px; background:#B1B4B6; Margin:30px 0 30px 0;" />', $html) ?? $html;
        $html = preg_replace('/<a /i', '<a style="color:#1D70B8;" ', $html) ?? $html;

        return $html;
    }
}
