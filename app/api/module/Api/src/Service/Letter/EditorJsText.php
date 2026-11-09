<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

/**
 * The words in EditorJS content, without markup: enough to tell whether a caseworker has written
 * anything, or whether what they saved is still the placeholder (VOL-7735).
 */
final class EditorJsText
{
    /**
     * @param array<string, mixed> $content EditorJS document
     */
    public static function plain(array $content): string
    {
        $parts = [];
        foreach ($content['blocks'] ?? [] as $block) {
            if (!is_array($block)) {
                continue;
            }
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            // paragraph/header text, list items (plain strings or {content}), captions
            array_walk_recursive($data, static function (mixed $value, int|string $key) use (&$parts): void {
                if (is_string($value) && (is_int($key) || in_array($key, ['text', 'content', 'caption'], true))) {
                    $parts[] = $value;
                }
            });
        }

        $text = html_entity_decode(strip_tags(implode(' ', $parts)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/[\s\x{00A0}]+/u', ' ', $text));
    }
}
