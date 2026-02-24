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
 * Handles conversion from EditorJS JSON to HTML for database storage
 */
class ConverterService
{
    private readonly Parser $parser;
    private readonly Renderer $renderer;

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
     * Clean HTML output after conversion
     *
     * @throws \RuntimeException if HTMLPurifier is not available
     */
    private function cleanOutputHtml(string $html): string
    {
        // HTMLPurifier is required for security
        if (!class_exists('\HTMLPurifier')) {
            throw new \RuntimeException('HTMLPurifier is required for secure HTML sanitization');
        }

        $purifier = new \HTMLPurifier(\HTMLPurifier_Config::createDefault());
        $cleanHtml = $purifier->purify($html);

        // Remove empty paragraphs and other empty block elements
        $cleanHtml = preg_replace('/<p[^>]*>\s*<\/p>/', '', $cleanHtml);
        $cleanHtml = preg_replace('/<div[^>]*>\s*<\/div>/', '', (string) $cleanHtml);

        return trim((string) $cleanHtml);
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
