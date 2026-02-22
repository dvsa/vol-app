<?php

namespace Dvsa\Olcs\Api\Service\Document\Parser;

/**
 * EditorJS JSON parser for VOL grabs
 *
 * Parses EditorJS JSON format to extract and replace [[placeholder]] tokens
 * with database values via the bookmark system.
 *
 */
class EditorJsParser implements ParserInterface
{
    /**
     * Regex pattern to match [[TOKEN_NAME]] placeholders
     * Only matches uppercase letters, numbers, and underscores (bookmark token format)
     */
    private const string GRAB_PATTERN = '/\[\[([A-Z0-9_]+)\]\]/';

    /**
     * Returns the file extension (json)
     *
     * @return string
     */
    public function getFileExtension()
    {
        return 'json';
    }

    /**
     * Extracts [[tokens]] from EditorJS JSON content
     *
     * @param string $content EditorJS JSON content
     *
     * @return array Array of unique token names found
     */
    public function extractTokens($content)
    {
        $tokens = [];

        try {
            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($decoded['blocks'])) {
                return $tokens;
            }

            // Iterate through blocks
            foreach ($decoded['blocks'] as $block) {
                $blockTokens = $this->extractTokensFromBlock($block);
                $tokens = array_merge($tokens, $blockTokens);
            }

            return array_unique($tokens);
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * Replace [[tokens]] with values in EditorJS JSON content
     *
     * @param string $content EditorJS JSON content
     * @param array  $data    Associative array of token => value data
     *
     * @return string Updated EditorJS JSON content
     */
    public function replace($content, $data)
    {
        try {
            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($decoded['blocks'])) {
                return $content;
            }

            // Process each block
            for ($i = 0; $i < count($decoded['blocks']); $i++) {
                $decoded['blocks'][$i] = $this->replaceTokensInBlock($decoded['blocks'][$i], $data);
            }

            return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Exception) {
            return $content;
        }
    }

    /**
     * Render an image (not applicable for EditorJS)
     *
     * @param string $binData Binary image data
     * @param int    $width   Image width
     * @param int    $height  Image height
     * @param string $type    Image type
     *
     * @return string
     * @throws \RuntimeException
     */
    public function renderImage($binData, $width, $height, $type): never
    {
        throw new \RuntimeException(
            'Image rendering not supported for EditorJS. Images are handled via EditorJS block structure.'
        );
    }

    /**
     * Extract tokens from a single EditorJS block
     *
     * @param array $block EditorJS block structure
     *
     * @return array Array of token names found in this block
     */
    private function extractTokensFromBlock(array $block): array
    {
        $tokens = [];
        $type = $block['type'] ?? null;

        switch ($type) {
            case 'paragraph':
            case 'header':
                $text = $block['data']['text'] ?? '';
                $tokens = $this->extractTokensFromString($text);
                break;

            case 'list':
                $items = $block['data']['items'] ?? [];
                foreach ($items as $item) {
                    $itemTokens = $this->extractTokensFromString($item);
                    $tokens = array_merge($tokens, $itemTokens);
                }
                break;
        }

        return $tokens;
    }

    /**
     * Extract tokens from a string using regex
     *
     * @param string $text Text to search
     *
     * @return array Array of token names found
     */
    private function extractTokensFromString(string $text): array
    {
        if (preg_match_all(self::GRAB_PATTERN, $text, $matches)) {
            return $matches[1]; // Capture group 1 contains token names
        }

        return [];
    }

    /**
     * Replace tokens in a single EditorJS block (returns new block)
     *
     * @param array $block EditorJS block structure
     * @param array $data  Associative array of token => value data
     *
     * @return array Updated block structure
     */
    private function replaceTokensInBlock(array $block, array $data): array
    {
        $type = $block['type'] ?? null;

        switch ($type) {
            case 'paragraph':
            case 'header':
                if (isset($block['data']['text'])) {
                    $block['data']['text'] = $this->replaceTokensInString(
                        $block['data']['text'],
                        $data
                    );
                }
                break;

            case 'list':
                if (isset($block['data']['items']) && is_array($block['data']['items'])) {
                    $newItems = [];
                    foreach ($block['data']['items'] as $item) {
                        $newItems[] = $this->replaceTokensInString($item, $data);
                    }
                    $block['data']['items'] = $newItems;
                }
                break;
        }

        return $block;
    }

    /**
     * Replace tokens in a string with their values
     *
     * @param string $text Text containing [[tokens]]
     * @param array  $data Associative array of token => value data
     *
     * @return string Text with tokens replaced
     */
    private function replaceTokensInString(string $text, array $data): string
    {
        // Simple approach: iterate through data and replace each token
        foreach ($data as $token => $value) {
            $placeholder = '[[' . $token . ']]';

            // Skip if this placeholder isn't in the text
            if (!str_contains($text, $placeholder)) {
                continue;
            }

            // Handle both simple string values and array format from bookmark system
            if (is_array($value)) {
                $content = $value['content'] ?? '';
                $preformatted = $value['preformatted'] ?? false;
            } else {
                $content = (string)$value;
                $preformatted = false;
            }

            // For EditorJS, convert newlines to <br> for HTML compatibility (unless preformatted)
            if (!$preformatted && !empty($content)) {
                $content = str_replace("\n", '<br>', $content);
            }

            // Replace the placeholder
            $text = str_replace($placeholder, $content, $text);
        }

        return $text;
    }
}
