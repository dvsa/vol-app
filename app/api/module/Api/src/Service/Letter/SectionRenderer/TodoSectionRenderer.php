<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;

/**
 * Renderer for LetterInstanceTodo entities.
 *
 * Outputs a single `<li>` containing the to-do description (EditorJS → HTML
 * with vol-grab replacement). The enclosing "What you need to do" block + `<ul>`
 * is rendered by LetterPreviewService::renderIssues() once per issue-type group.
 */
class TodoSectionRenderer extends AbstractSectionRenderer
{
    /**
     * @param object $entity
     * @param array $context
     * @return string HTML output
     * @throws \InvalidArgumentException if entity is not supported
     */
    #[\Override]
    public function render(object $entity, array $context = []): string
    {
        if (!$this->supports($entity)) {
            throw new \InvalidArgumentException(
                'TodoSectionRenderer only supports LetterInstanceTodo entities'
            );
        }

        /** @var LetterInstanceTodo $entity */
        $todoVersion = $entity->getLetterTodoVersion();
        // The ORM column is declared type=json (so the docblock says array|null)
        // but in some hydration paths it comes back as a JSON-encoded string. The
        // type is therefore widened to mixed here so the defensive normalisation
        // below isn't flagged as dead code by Psalm.
        /** @var mixed $description */
        $description = $todoVersion?->getDescription();

        if (empty($description)) {
            return '';
        }

        // Same defensive shape as LetterInstanceIssue::getEffectiveContent —
        // normalise to array so convertEditorJsToHtml's strict array signature
        // is honoured.
        if (is_string($description)) {
            $decoded = json_decode($description, true);
            $description = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        }

        if (!is_array($description) || empty($description)) {
            return '';
        }

        $body = $this->convertEditorJsToHtml($description, $context);
        if ($body === '') {
            return '';
        }

        return '<li class="todo-item">' . $body . '</li>';
    }

    #[\Override]
    public function supports(object $entity): bool
    {
        return $entity instanceof LetterInstanceTodo;
    }
}
