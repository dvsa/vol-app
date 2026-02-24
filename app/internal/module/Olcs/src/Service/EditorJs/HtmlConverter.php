<?php

namespace Olcs\Service\EditorJs;

use Olcs\Form\Element\EditorJs;

/**
 * Simple HTML to EditorJS JSON converter for app/internal
 *
 * Handles conversion of existing HTML content (mainly <p> tags) to EditorJS JSON format
 */
class HtmlConverter
{
    /**
     * Convert HTML to EditorJS JSON format
     *
     * @param string $html
     * @return string JSON string
     */
    public function convertHtmlToJson(string $html): string
    {
        if (empty($html)) {
            return json_encode([
                'blocks' => [],
                'version' => EditorJs::EDITORJS_VERSION
            ]);
        }

        $cleanedHtml = $this->cleanInputHtml($html);
        $blocks = $this->parseHtmlToBlocks($cleanedHtml);

        $editorData = [
            'time' => time() * 1000, // EditorJS expects milliseconds
            'blocks' => $blocks,
            'version' => EditorJs::EDITORJS_VERSION
        ];

        return json_encode($editorData, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Check if string contains HTML tags
     *
     * @param string $string
     * @return bool
     */
    public function isHtml(string $string): bool
    {
        return $string !== strip_tags($string);
    }

    /**
     * Check if string is valid EditorJS JSON
     *
     * @param string $string
     * @return bool
     */
    public function isValidEditorJsJson(string $string): bool
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

    /**
     * Clean HTML before conversion (fix TinyMCE encoding issues)
     *
     * @param string $html
     * @return string
     */
    private function cleanInputHtml(string $html): string
    {
        // Fix corrupted non-breaking spaces from TinyMCE (Â characters)
        $html = str_replace('Â', '&nbsp;', $html);

        // Fix other common encoding issues
        $html = str_replace([
            'â€™', // Smart apostrophe
            'â€œ', // Smart quote open
            'â€',  // Smart quote close
            'â€"'  // Em/En dash
        ], [
            "'",
            '"',
            '"',
            '—'
        ], $html);

        // Normalize multiple spaces and line breaks
        $html = preg_replace('/\s{2,}/', ' ', $html);

        // Remove empty paragraph tags
        $html = preg_replace('/<p>\s*<\/p>/', '', (string) $html);

        return trim((string) $html);
    }

    /**
     * Parse content into EditorJS blocks (handles both HTML and plain text)
     *
     * @param string $content
     * @return array
     */
    private function parseHtmlToBlocks(string $content): array
    {
        $blocks = [];

        // If no HTML tags, treat as plain text
        if (!preg_match('/<[^>]+>/', $content)) {
            $textContent = $this->extractTextContent($content);
            if (!empty(trim($textContent))) {
                $blocks[] = [
                    'id' => 'converted-' . uniqid(),
                    'type' => 'paragraph',
                    'data' => [
                        'text' => $textContent
                    ]
                ];
            }
            return $blocks;
        }

        // Parse HTML using DOMDocument
        $dom = new \DOMDocument();
        $dom->encoding = 'UTF-8';

        // Suppress errors for malformed HTML and load content
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Find all block-level elements in order
        $blockElements = $xpath->query('//p | //ul | //ol | //h1 | //h2 | //h3 | //h4 | //h5 | //h6');

        foreach ($blockElements as $element) {
            $block = $this->convertDomElementToBlock($element);
            if ($block) {
                $blocks[] = $block;
            }
        }

        // If no blocks were created, treat the whole content as a paragraph
        if (empty($blocks)) {
            $textContent = $this->extractTextContent($content);
            if (!empty(trim($textContent))) {
                $blocks[] = [
                    'id' => 'converted-' . uniqid(),
                    'type' => 'paragraph',
                    'data' => [
                        'text' => $textContent
                    ]
                ];
            }
        }

        return $blocks;
    }

    /**
     * Convert a DOM element to an EditorJS block
     *
     * @param \DOMElement $element
     * @return array|null
     */
    private function convertDomElementToBlock(\DOMElement $element): ?array
    {
        $tagName = strtolower($element->tagName);

        switch ($tagName) {
            case 'p':
                $textContent = $this->extractTextContent($this->getInnerHTML($element));
                if (!empty(trim($textContent))) {
                    return [
                        'id' => 'converted-' . uniqid(),
                        'type' => 'paragraph',
                        'data' => [
                            'text' => $textContent
                        ]
                    ];
                }
                break;

            case 'ul':
            case 'ol':
                $items = $this->parseListItems($this->getInnerHTML($element));
                if (!empty($items)) {
                    return [
                        'id' => 'converted-' . uniqid(),
                        'type' => 'list',
                        'data' => [
                            'style' => $tagName === 'ul' ? 'unordered' : 'ordered',
                            'items' => $items
                        ]
                    ];
                }
                break;

            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                $textContent = $this->extractTextContent($this->getInnerHTML($element));
                if (!empty(trim($textContent))) {
                    return [
                        'id' => 'converted-' . uniqid(),
                        'type' => 'header',
                        'data' => [
                            'text' => $textContent,
                            'level' => (int)substr($tagName, 1)
                        ]
                    ];
                }
                break;
        }

        return null;
    }

    /**
     * Get inner HTML of a DOM element
     *
     * @param \DOMElement $element
     * @return string
     */
    private function getInnerHTML(\DOMElement $element): string
    {
        $innerHTML = '';
        foreach ($element->childNodes as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }
        return $innerHTML;
    }

    /**
     * Parse list items from HTML list content
     *
     * @param string $listHtml
     * @return array
     */
    private function parseListItems(string $listHtml): array
    {
        $items = [];

        if (preg_match_all('/<li[^>]*>(.*?)<\/li>/si', $listHtml, $matches)) {
            foreach ($matches[1] as $itemContent) {
                $textContent = $this->extractTextContent($itemContent);
                if (!empty(trim($textContent))) {
                    $items[] = $textContent;
                }
            }
        }

        return $items;
    }

    /**
     * Extract text content, preserving basic formatting that EditorJS supports
     *
     * @param string $html
     * @return string
     */
    private function extractTextContent(string $html): string
    {
        // Convert common HTML entities
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize formatting tags to EditorJS standard format
        $html = str_replace(['<strong>', '</strong>'], ['<b>', '</b>'], $html);
        $html = str_replace(['<em>', '</em>'], ['<i>', '</i>'], $html);

        // Preserve EditorJS-supported inline formatting: <b>, <i>, <u>, <s>, <mark>, <code>, <a>
        $allowedTags = '<b><i><u><s><mark><code><a>';
        $text = strip_tags($html, $allowedTags);

        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim((string) $text);
    }
}
