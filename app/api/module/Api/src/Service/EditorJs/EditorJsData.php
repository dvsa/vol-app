<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\EditorJs;

/**
 * Shape helpers for EditorJS data arrays.
 *
 * The Setono parser mandates the envelope {time: int, version: string,
 * blocks: [{id, type, data}, ...]} but hand-authored content (DB seeds, imports,
 * direct API calls) frequently omits 'time' and per-block 'id'. These helpers are
 * static so command handlers can use them without service wiring.
 */
final class EditorJsData
{
    private function __construct()
    {
    }

    /**
     * Fill in the envelope fields the parser mandates but hand-authored content
     * omits. Conformant data passes through untouched.
     */
    public static function normalize(array $data): array
    {
        if (!isset($data['time']) || !is_int($data['time'])) {
            $data['time'] = 0;
        }

        if (!isset($data['version']) || !is_string($data['version'])) {
            $data['version'] = 'unknown';
        }

        if (isset($data['blocks']) && is_array($data['blocks'])) {
            foreach ($data['blocks'] as $i => $block) {
                if (is_array($block) && (!isset($block['id']) || !is_string($block['id']) || $block['id'] === '')) {
                    $data['blocks'][$i]['id'] = 'gen-' . $i;
                }
            }
        }

        return $data;
    }

    /**
     * Structural check: 'blocks' must be an array of blocks each carrying a string
     * 'type' and an array 'data'. Envelope fields (time/version/id) are NOT required
     * here — normalize() can supply those; this guards against data that isn't
     * EditorJS-shaped at all.
     */
    public static function isValidShape(array $data): bool
    {
        if (!isset($data['blocks']) || !is_array($data['blocks'])) {
            return false;
        }

        foreach ($data['blocks'] as $block) {
            if (!is_array($block) || !isset($block['type']) || !is_string($block['type'])) {
                return false;
            }
            if (!isset($block['data']) || !is_array($block['data'])) {
                return false;
            }
        }

        return true;
    }
}
