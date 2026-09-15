<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

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

        return $this->letterTodoVersion?->getDescriptionAsArray() ?? [];
    }

    public function hasBeenEdited(): bool
    {
        return !empty($this->editedDescription);
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
