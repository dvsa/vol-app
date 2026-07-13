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

        $purifier = new \HTMLPurifier($this->buildPurifierConfig());
        $cleanHtml = $purifier->purify($html);

        // Remove empty paragraphs and other empty block elements
        $cleanHtml = preg_replace('/<p[^>]*>\s*<\/p>/', '', $cleanHtml);
        $cleanHtml = preg_replace('/<div[^>]*>\s*<\/div>/', '', (string) $cleanHtml);

        return trim((string) $cleanHtml);
    }


    /**
     * Build the HTMLPurifier config.
     *
     * Points the definition-cache serializer at a writable directory. HTMLPurifier's default is
     * inside the vendor tree, which is read-only in deployed containers and so triggers
     * "Directory ... not writable" warnings and forces the definition cache to be regenerated on
     * every call.
     */
    public function buildPurifierConfig(): \HTMLPurifier_Config
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', sys_get_temp_dir());

        // Letter chrome embeds the OTC logo as an inline base64 image. HTMLPurifier's
        // data-URI scheme handler only admits verified image payloads (png/gif/jpeg),
        // so allowing the scheme does not open script or arbitrary-content vectors.
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
            'tel' => true,
            'data' => true,
        ]);

        return $config;
    }


    /**
     * Normalise EditorJS data to the envelope shape the parser mandates.
     *
     * Hand-authored content (DB seeds, imports) often omits the top-level 'time' and
     * per-block 'id' fields, which the Setono parser treats as required. Fill them in
     * so such content renders instead of failing the whole conversion; content saved
     * through the EditorJS editor already conforms and passes through untouched.
     */
    public function normalize(array $data): array
    {
        return EditorJsData::normalize($data);
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
