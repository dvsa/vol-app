<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection;

/**
 * Renderer for LetterInstanceSection entities
 *
 * Handles content sections that contain EditorJS JSON in
 * edited_content or default_content fields.
 */
class ContentSectionRenderer extends AbstractSectionRenderer
{
    /**
     * Render the section content to HTML
     *
     * @param object $entity
     * @return string HTML output wrapped in <div class="section">
     * @throws \InvalidArgumentException if entity is not supported
     */
    public function render(object $entity): string
    {
        if (!$this->supports($entity)) {
            throw new \InvalidArgumentException(
                'ContentSectionRenderer only supports LetterInstanceSection entities'
            );
        }

        /** @var LetterInstanceSection $entity */
        $content = $entity->getEffectiveContent();

        if (empty($content)) {
            return '';
        }

        $html = $this->convertEditorJsToHtml($content);

        return $this->wrapInSection($html, 'section');
    }

    /**
     * Check if this renderer supports the given entity
     *
     * @param object $entity
     * @return bool
     */
    public function supports(object $entity): bool
    {
        return $entity instanceof LetterInstanceSection;
    }
}
