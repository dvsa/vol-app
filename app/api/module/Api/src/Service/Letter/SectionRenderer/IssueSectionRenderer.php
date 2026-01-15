<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;

/**
 * Renderer for LetterInstanceIssue entities
 *
 * Renders issues with an H4 heading followed by the EditorJS body content.
 */
class IssueSectionRenderer extends AbstractSectionRenderer
{
    /**
     * Render the issue content to HTML
     *
     * Outputs: sub-heading (H4) + body content for individual issue
     *
     * @param object $entity
     * @return string HTML output
     * @throws \InvalidArgumentException if entity is not supported
     */
    public function render(object $entity): string
    {
        if (!$this->supports($entity)) {
            throw new \InvalidArgumentException(
                'IssueSectionRenderer only supports LetterInstanceIssue entities'
            );
        }

        /** @var LetterInstanceIssue $entity */
        $heading = $entity->getHeading();
        $content = $entity->getEffectiveContent();

        $html = '<div class="issue">';

        // Add issue sub-heading
        if (!empty($heading)) {
            $html .= '<h4 class="issue-heading">' . htmlspecialchars($heading) . '</h4>';
        }

        // Add body content
        if (!empty($content)) {
            $html .= '<div class="issue-body">' . $this->convertEditorJsToHtml($content) . '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Check if this renderer supports the given entity
     *
     * @param object $entity
     * @return bool
     */
    public function supports(object $entity): bool
    {
        return $entity instanceof LetterInstanceIssue;
    }
}
