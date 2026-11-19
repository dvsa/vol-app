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

    /**
     * Whether two EditorJS documents print the same: the same blocks, in order, with the same
     * content, formatting and settings. The editor's own bookkeeping (the time it stamps on every
     * save, its version, block ids) isn't part of what's printed.
     *
     * @param array<string, mixed> $a EditorJS document
     * @param array<string, mixed> $b EditorJS document
     */
    public static function sameContent(array $a, array $b): bool
    {
        $printed = static fn (array $content): array => self::keysInOrder(array_map(
            static fn (mixed $block): mixed => is_array($block) ? array_diff_key($block, ['id' => true]) : $block,
            array_values(is_array($content['blocks'] ?? null) ? $content['blocks'] : [])
        ));

        return $printed($a) === $printed($b);
    }

    /**
     * The same keys in the same order, so a strict comparison doesn't depend on the order they
     * were written in (== would, but also counts "1" and "01" as the same)
     */
    private static function keysInOrder(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        if (!array_is_list($value)) {
            ksort($value);
        }

        return array_map(self::keysInOrder(...), $value);
    }
}
