<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterTodoVersion Entity
 */
#[ORM\Table(name: 'letter_todo_version')]
#[ORM\Entity]
class LetterTodoVersion extends AbstractLetterTodoVersion
{
    /**
     * The standing wording, normalised to an array.
     *
     * The counterpart of LetterSectionVersion::getDefaultContentAsArray, and it exists for the
     * same reason: the column is mapped `type: json`, but some hydration paths hand back the
     * JSON-encoded string instead, so callers cannot rely on the shape.
     *
     * Total by construction -- anything that is not an array comes back as []. Callers declare
     * `: array` and the renderer no longer guards the type itself, so letting a scalar through
     * here would turn today's empty render into a TypeError.
     */
    public function getDescriptionAsArray(): array
    {
        $description = $this->description;

        if (empty($description)) {
            return [];
        }

        if (is_string($description)) {
            $decoded = json_decode($description, true);

            return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        }

        return is_array($description) ? $description : [];
    }

    /**
     * Check if this version is published
     *
     * @return bool
     */
    public function isPublished()
    {
        if ($this->publishFrom === null) {
            return true;
        }

        return $this->publishFrom <= new \DateTime();
    }

    /**
     * Check if this version is embargoed
     *
     * @return bool
     */
    public function isEmbargoed()
    {
        return !$this->isPublished();
    }

    /**
     * Check if this version is the current version
     *
     * @return bool
     */
    public function isCurrent()
    {
        $todo = $this->getLetterTodo();
        if (!$todo) {
            return false;
        }

        return $todo->getCurrentVersion() === $this;
    }

    /**
     * Lock this version
     *
     * @return self
     */
    public function lock()
    {
        $this->isLocked = true;
        return $this;
    }

    /**
     * Unlock this version
     *
     * @return self
     */
    public function unlock()
    {
        $this->isLocked = false;
        return $this;
    }
}
