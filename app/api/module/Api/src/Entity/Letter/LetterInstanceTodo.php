<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Service\Letter\EditorJsText;

/**
 * LetterInstanceTodo Entity
 *
 * A to-do pulled into one letter. The wording comes from the shared LetterTodoVersion unless a
 * caseworker has overridden it here, in which case the override applies to THIS LETTER ONLY --
 * letter_todo_version is never written by an edit. That is the same arrangement sections, issues
 * and appendices already use.
 */
#[ORM\Table(name: 'letter_instance_todo')]
#[ORM\Entity]
class LetterInstanceTodo extends AbstractLetterInstanceTodo
{
    /**
     * The wording to render: the caseworker's edit if there is one, otherwise the standing text.
     *
     * Total by construction -- see LetterTodoVersion::getDescriptionAsArray. The edited column is
     * mapped `type: json` but can arrive double-encoded, exactly like the sibling entities'
     * edited_content.
     */
    public function getEffectiveDescription(): array
    {
        if (!empty($this->editedDescription)) {
            $description = $this->editedDescription;

            if (is_string($description)) {
                $decoded = json_decode($description, true);

                return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
            }

            return is_array($description) ? $description : [];
        }

        if (!empty($this->generatedDescription) && is_array($this->generatedDescription)) {
            return $this->generatedDescription;
        }

        return $this->letterTodoVersion?->getDescriptionAsArray() ?? [];
    }

    public function requiresInput(): bool
    {
        return (bool) $this->letterTodoVersion?->getRequiresInput();
    }

    public function hasBeenEdited(): bool
    {
        return !empty($this->editedDescription);
    }

    /**
     * A to-do the caseworker wrote on an Other issue, with no library version behind it (VOL-7735)
     */
    public function isCustom(): bool
    {
        return $this->letterTodoVersion === null;
    }

    /**
     * Whether this to-do still needs the caseworker before the letter can be sent: a flagged
     * library to-do left unedited, or a custom to-do with nothing written in it.
     */
    public function isInputPending(): bool
    {
        if ($this->isCustom()) {
            return EditorJsText::plain($this->getEffectiveDescription()) === '';
        }

        return $this->requiresInput() && !$this->hasBeenEdited();
    }

    /**
     * 1-based place among its issue's custom to-dos, in display order; null for a library to-do.
     */
    public function getCustomIndex(): ?int
    {
        if (!$this->isCustom() || $this->letterInstanceIssue === null) {
            return null;
        }

        $custom = array_values(array_filter(
            $this->letterInstanceIssue->getLetterInstanceTodos()->toArray(),
            static fn(LetterInstanceTodo $todo): bool => $todo->isCustom()
        ));
        usort($custom, static fn(LetterInstanceTodo $a, LetterInstanceTodo $b): int => $a->getDisplayOrder() <=> $b->getDisplayOrder());

        $index = array_search($this, $custom, true);

        return $index === false ? null : $index + 1;
    }

    /**
     * How a custom to-do is named away from its issue: "<issue heading>: to-do N".
     */
    public function getCustomLabel(): ?string
    {
        $index = $this->getCustomIndex();

        return $index === null ? null : $this->letterInstanceIssue->getDisplayName() . ': to-do ' . $index;
    }

    public function clearEditedDescription(): self
    {
        $this->editedDescription = null;

        return $this;
    }

    public function setEditedDescriptionFromArray(array $description): self
    {
        $this->editedDescription = $description;

        return $this;
    }
}
