<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Traits;

use Dvsa\Olcs\Api\Domain\EditorJsConverterAwareInterface;
use Dvsa\Olcs\Api\Domain\EditorJsConverterAwareTrait;

/**
 * EditorJS conversion trait for command handlers
 * 
 * Combines the aware interface functionality with utility methods
 */
trait EditorJsConversionTrait
{
    use EditorJsConverterAwareTrait;

    /**
     * Convert comment text from JSON to HTML if needed
     * 
     * @param string $commentText
     * @return string
     */
    protected function convertCommentForStorage(string $commentText): string
    {
        // Convert JSON to HTML if needed
        if ($this->isValidEditorJsJson($commentText)) {
            try {
                return $this->getConverterService()->convertJsonToHtml($commentText);
            } catch (\Exception $e) {
                // If conversion fails, use as-is
                return $commentText;
            }
        }
        
        return $commentText;
    }

    /**
     * Check if string is valid EditorJS JSON
     * 
     * @param string $string
     * @return bool
     */
    protected function isValidEditorJsJson(string $string): bool
    {
        $data = json_decode($string, true);
        
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