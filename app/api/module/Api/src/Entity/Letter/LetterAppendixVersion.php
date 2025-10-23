<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterAppendixVersion Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_appendix_version")
 */
class LetterAppendixVersion extends AbstractLetterAppendixVersion
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
        $appendix = $this->getLetterAppendix();
        if (!$appendix) {
            return false;
        }

        return $appendix->getCurrentVersion() === $this;
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

    /**
     * Check if this version has a document attached
     *
     * @return bool
     */
    public function hasDocument()
    {
        return $this->document !== null;
    }

    /**
     * Get document filename
     *
     * @return string|null
     */
    public function getDocumentFilename()
    {
        if (!$this->hasDocument()) {
            return null;
        }

        return $this->document->getFilename();
    }

    /**
     * Get document identifier
     *
     * @return string|null
     */
    public function getDocumentIdentifier()
    {
        if (!$this->hasDocument()) {
            return null;
        }

        return $this->document->getIdentifier();
    }
}
