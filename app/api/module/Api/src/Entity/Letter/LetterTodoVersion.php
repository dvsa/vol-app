<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterTodoVersion Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_todo_version")
 */
class LetterTodoVersion extends AbstractLetterTodoVersion
{
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
