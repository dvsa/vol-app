<?php

namespace Dvsa\Olcs\Api\Service\EditorJs;

use Setono\EditorJS\Parser\Parser;
use Setono\EditorJS\Renderer\Renderer;
use Setono\EditorJS\BlockRenderer\HeaderBlockRenderer;
use Setono\EditorJS\BlockRenderer\ListBlockRenderer;
use Setono\EditorJS\BlockRenderer\ParagraphBlockRenderer;

/**
 * EditorJS Converter Service
 * 
 * Handles conversion between EditorJS JSON and HTML for submission comments
 */
class ConverterService
{
    private Parser $parser;
    private Renderer $renderer;

    public function __construct()
    {
        $this->parser = new Parser();
        $this->renderer = new Renderer();
        
        // Add block renderers for the types we support
        $this->renderer->add(new ParagraphBlockRenderer());
        $this->renderer->add(new HeaderBlockRenderer());
        $this->renderer->add(new ListBlockRenderer());
    }

    /**
     * Convert EditorJS JSON to HTML for database storage
     */
    public function convertJsonToHtml(string $jsonData): string
    {
        if (empty($jsonData)) {
            return '';
        }

        try {
            $parserResult = $this->parser->parse($jsonData);
            $html = $this->renderer->render($parserResult);
            return $this->cleanOutputHtml($html);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to convert JSON to HTML: ' . $e->getMessage());
        }
    }

    /**
     * Convert HTML to EditorJS JSON for frontend editing
     */
    public function convertHtmlToJson(string $html): string
    {
        if (empty($html)) {
            return json_encode(['blocks' => [], 'version' => '2.28.2']);
        }

        $cleanedHtml = $this->cleanInputHtml($html);

        try {
            $blocks = $this->parseHtmlToBlocks($cleanedHtml);
            
            $editorData = [
                'time' => time() * 1000, // EditorJS expects milliseconds
                'blocks' => $blocks,
                'version' => '2.28.2'
            ];

            return json_encode($editorData);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to convert HTML to JSON: ' . $e->getMessage());
        }
    }

    /**
     * Clean HTML before conversion (fix TinyMCE encoding issues)
     */
    private function cleanInputHtml(string $html): string
    {
        // Fix corrupted non-breaking spaces from TinyMCE
        $html = str_replace('Â', '&nbsp;', $html);
        
        // Fix other common encoding issues
        $html = str_replace([
            'â€™', // Smart apostrophe
            'â€œ', // Smart quote open
            'â€',  // Smart quote close
            'â€"', // Em dash
            'â€"'  // En dash
        ], [
            "'",
            '"',
            '"',
            '—',
            '–'
        ], $html);

        // Normalize multiple spaces
        $html = preg_replace('/\s{2,}/', ' ', $html);
        
        // Remove empty elements
        $html = preg_replace('/<p>\s*<\/p>/', '', $html);
        
        return trim($html);
    }

    /**
     * Clean HTML output after conversion
     */
    private function cleanOutputHtml(string $html): string
    {
        // Apply HTML purification if available
        if (class_exists('\HTMLPurifier')) {
            $purifier = new \HTMLPurifier(\HTMLPurifier_Config::createDefault());
            return $purifier->purify($html);
        }
        
        // Basic HTML sanitization as fallback
        return strip_tags($html, '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li>');
    }

    /**
     * Parse cleaned HTML into EditorJS blocks
     */
    private function parseHtmlToBlocks(string $html): array
    {
        $blocks = [];
        
        // Use DOMDocument for reliable HTML parsing
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            // If no body, try parsing the HTML directly
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML($html);
            $this->processNodes($fragment->childNodes, $blocks);
        } else {
            $this->processNodes($body->childNodes, $blocks);
        }

        // If no blocks were created, create a paragraph block with the text content
        if (empty($blocks) && !empty(trim(strip_tags($html)))) {
            $blocks[] = [
                'id' => $this->generateBlockId(),
                'type' => 'paragraph',
                'data' => [
                    'text' => trim(strip_tags($html))
                ]
            ];
        }

        return $blocks;
    }

    /**
     * Process DOM nodes and convert to EditorJS blocks
     */
    private function processNodes(\DOMNodeList $nodes, array &$blocks): void
    {
        foreach ($nodes as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            switch (strtolower($node->nodeName)) {
                case 'p':
                    $text = $this->getTextContent($node);
                    if (!empty(trim($text))) {
                        $blocks[] = [
                            'id' => $this->generateBlockId(),
                            'type' => 'paragraph',
                            'data' => [
                                'text' => $text
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
                    $text = $this->getTextContent($node);
                    if (!empty(trim($text))) {
                        $level = (int) substr($node->nodeName, 1);
                        $blocks[] = [
                            'id' => $this->generateBlockId(),
                            'type' => 'header',
                            'data' => [
                                'text' => $text,
                                'level' => $level
                            ]
                        ];
                    }
                    break;

                case 'ul':
                case 'ol':
                    $items = [];
                    $listItems = $node->getElementsByTagName('li');
                    foreach ($listItems as $li) {
                        $items[] = $this->getTextContent($li);
                    }
                    
                    if (!empty($items)) {
                        $blocks[] = [
                            'id' => $this->generateBlockId(),
                            'type' => 'list',
                            'data' => [
                                'style' => $node->nodeName === 'ul' ? 'unordered' : 'ordered',
                                'items' => $items
                            ]
                        ];
                    }
                    break;
            }
        }
    }

    /**
     * Get text content from DOM node, preserving basic formatting
     */
    private function getTextContent(\DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $html .= htmlspecialchars($child->textContent);
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                switch (strtolower($child->nodeName)) {
                    case 'strong':
                    case 'b':
                        $html .= '<b>' . $this->getTextContent($child) . '</b>';
                        break;
                    case 'em':
                    case 'i':
                        $html .= '<i>' . $this->getTextContent($child) . '</i>';
                        break;
                    case 'u':
                        $html .= '<u>' . $this->getTextContent($child) . '</u>';
                        break;
                    default:
                        $html .= $this->getTextContent($child);
                }
            }
        }
        return $html;
    }

    /**
     * Generate unique block ID
     */
    private function generateBlockId(): string
    {
        return uniqid('block_', true);
    }

    /**
     * Validate EditorJS JSON structure
     */
    public function validateJsonStructure(string $jsonData): bool
    {
        $data = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        // Check required structure
        if (!isset($data['blocks']) || !is_array($data['blocks'])) {
            return false;
        }

        // Validate each block
        foreach ($data['blocks'] as $block) {
            if (!isset($block['type']) || !isset($block['data'])) {
                return false;
            }
        }

        return true;
    }
}