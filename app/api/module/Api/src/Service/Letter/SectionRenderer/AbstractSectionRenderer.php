<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Service\EditorJs\ConverterService;
use Dvsa\Olcs\Api\Service\Letter\VolGrabReplacementService;

/**
 * Abstract base class for letter section renderers
 *
 * Provides common functionality for converting EditorJS JSON content to HTML.
 */
abstract class AbstractSectionRenderer implements SectionRendererInterface
{
    public function __construct(
        protected readonly ConverterService $converterService,
        protected readonly VolGrabReplacementService $volGrabReplacementService
    ) {
    }

    /**
     * Convert EditorJS content array to HTML
     *
     * @param array $content EditorJS content as associative array
     * @param array $context Context for vol-grab replacement (licence, application, etc.)
     * @return string HTML output
     */
    protected function convertEditorJsToHtml(array $content, array $context = []): string
    {
        if (empty($content)) {
            return '';
        }

        $jsonString = json_encode($content);
        if ($jsonString === false) {
            return '';
        }

        // Replace vol-grab placeholders before HTML conversion
        $jsonString = $this->volGrabReplacementService->replaceGrabs($jsonString, $context);

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
