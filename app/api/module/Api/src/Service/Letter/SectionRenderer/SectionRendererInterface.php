<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\SectionRenderer;

/**
 * Interface for letter section renderers
 *
 * Each renderer handles a specific letter instance entity type
 * (section, issue, todo, appendix) and converts its content to HTML.
 */
interface SectionRendererInterface
{
    /**
     * Render the entity's content to HTML
     *
     * @param object $entity A letter instance entity (LetterInstanceSection,
     *                       LetterInstanceIssue, LetterInstanceTodo, or LetterInstanceAppendix)
     * @param array $context Optional context for vol-grab replacement (licence, application, etc.)
     * @return string HTML representation of the content
     */
    public function render(object $entity, array $context = []): string;

    /**
     * Check if this renderer supports the given entity
     *
     * @param object $entity
     * @return bool
     */
    public function supports(object $entity): bool;
}
