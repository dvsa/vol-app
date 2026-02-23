<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceAppendix;

/**
 * Renderer for LetterInstanceAppendix entities
 *
 * Renders PDF appendices as placeholder cards and editable appendices
 * with their EditorJS content.
 */
class AppendixSectionRenderer extends AbstractSectionRenderer
{
    /**
     * Render the appendix content to HTML
     *
     * @param object $entity
     * @param array $context Context for vol-grab replacement (licence, application, etc.)
     * @return string HTML output
     * @throws \InvalidArgumentException if entity is not supported
     */
    public function render(object $entity, array $context = []): string
    {
        if (!$this->supports($entity)) {
            throw new \InvalidArgumentException(
                'AppendixSectionRenderer only supports LetterInstanceAppendix entities'
            );
        }

        /** @var LetterInstanceAppendix $entity */

        // PDF type: render placeholder card
        if ($entity->isPdf()) {
            $name = htmlspecialchars($entity->getName());
            return '<div class="appendix appendix--pdf">'
                 . '<p class="appendix-pdf-note"><strong>PDF Appendix: ' . $name . '</strong>'
                 . ' &mdash; This appendix will be appended to the final document.</p>'
                 . '</div>';
        }

        // Editable type: render EditorJS content
        $content = $entity->getEffectiveContent();
        $html = '<div class="appendix appendix--editable">';
        $html .= '<h3 class="appendix-heading">' . htmlspecialchars($entity->getName()) . '</h3>';
        if (!empty($content)) {
            $html .= '<div class="appendix-body">' . $this->convertEditorJsToHtml($content, $context) . '</div>';
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
        return $entity instanceof LetterInstanceAppendix;
    }
}
