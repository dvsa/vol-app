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
        $description = $todoVersion?->getDescription();

        if (empty($description)) {
            return '';
        }

        // The `description` column is declared type=json but can come back as a
        // double-encoded JSON string in some hydration paths — same defensive shape
        // as LetterInstanceIssue::getEffectiveContent. Normalise to array here so
        // convertEditorJsToHtml's strict array signature is honoured.
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
