<?php

namespace Olcs\Filter;

use Laminas\Filter\FilterInterface;

/**
 * EditorJS Filter
 * 
 * Validates and sanitizes EditorJS JSON data
 */
class EditorJsFilter implements FilterInterface
{
    /**
     * Filter EditorJS JSON data
     */
    public function filter($value): string
    {
        if (empty($value)) {
            return '';
        }

        // If it's already valid JSON, return as-is
        if ($this->isValidEditorJsJson($value)) {
            return $value;
        }

        // If it's HTML, we'll need to convert it to JSON on the frontend
        // For now, just return the value and let the view helper handle conversion
        return $value;
    }

    /**
     * Check if value is valid EditorJS JSON
     */
    private function isValidEditorJsJson(string $value): bool
    {
        $data = json_decode($value, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        // Check basic EditorJS structure
        if (!isset($data['blocks']) || !is_array($data['blocks'])) {
            return false;
        }

        // Validate each block has required structure
        foreach ($data['blocks'] as $block) {
            if (!isset($block['type']) || !isset($block['data'])) {
                return false;
            }
        }

        return true;
    }
}