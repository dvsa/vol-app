<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;

/**
 * Renderer for LetterInstanceTodo entities.
 *
 * Outputs a single block `<div>` containing the to-do description (EditorJS → HTML
 * with vol-grab replacement). The enclosing "What you need to do" heading + container
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
        // The caseworker's edit for this letter if there is one, otherwise the standing wording.
        // getEffectiveDescription() is total -- it absorbs the double-encoded-JSON hydration case
        // and returns [] for anything it cannot make an array of -- so the normalisation that used
        // to live here now belongs to the entity, next to the data it is defending.
        $description = $entity->getEffectiveDescription();

        if ($description === []) {
            return '';
        }

        $body = $this->convertEditorJsToHtml($description, $context);
        if ($body === '') {
            return '';
        }

        // VOL-7280: a block, not a list item — as an <li> the whole to-do became a
        // bullet and bullets inside its own content demoted to hollow nested ones.
        return '<div class="todo-item">' . $body . '</div>';
    }

    #[\Override]
    public function supports(object $entity): bool
    {
        return $entity instanceof LetterInstanceTodo;
    }
}
