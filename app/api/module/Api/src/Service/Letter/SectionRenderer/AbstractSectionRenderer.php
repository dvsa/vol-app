<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Service\EditorJs\ConverterService;

/**
 * Abstract base class for letter section renderers
 *
 * Provides common functionality for converting EditorJS JSON content to HTML.
 */
abstract class AbstractSectionRenderer implements SectionRendererInterface
{
    public function __construct(
        protected readonly ConverterService $converterService
    ) {
    }

    /**
     * Convert EditorJS content array to HTML
     *
     * @param array $content EditorJS content as associative array
     * @return string HTML output
     */
    protected function convertEditorJsToHtml(array $content): string
    {
        if (empty($content)) {
            return '';
        }

        $jsonString = json_encode($content);
        if ($jsonString === false) {
            return '';
        }

        return $this->converterService->convertJsonToHtml($jsonString);
    }

    /**
     * Wrap content in a section container with optional CSS class
     *
     * @param string $html The HTML content
     * @param string $cssClass CSS class for the container div
     * @return string Wrapped HTML
     */
    protected function wrapInSection(string $html, string $cssClass = 'section'): string
    {
        if (empty($html)) {
            return '';
        }

        return '<div class="' . htmlspecialchars($cssClass) . '">' . $html . '</div>';
    }
}
