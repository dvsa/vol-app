<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterSectionVersion Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_section_version")
 */
class LetterSectionVersion extends AbstractLetterSectionVersion
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
        $section = $this->getLetterSection();
        if (!$section) {
            return false;
        }

        return $section->getCurrentVersion() === $this;
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
     * Check if content is valid based on length constraints
     *
     * @param string $content
     * @return bool
     */
    public function isContentValid($content)
    {
        $length = strlen($content);

        if ($this->minLength !== null && $length < $this->minLength) {
            return false;
        }

        if ($this->maxLength !== null && $length > $this->maxLength) {
            return false;
        }

        return true;
    }

    /**
     * Get default content as array (for EditorJS)
     *
     * @return array
     */
    public function getDefaultContentAsArray()
    {
        return $this->defaultContent ?: [];
    }

    /**
     * Set default content from array (for EditorJS)
     *
     * @param array $content
     * @return self
     */
    public function setDefaultContentFromArray(array $content)
    {
        $this->defaultContent = $content;
        return $this;
    }

    /**
     * Check if this version applies to NI
     *
     * @return bool
     */
    public function appliesToNi()
    {
        return $this->isNi;
    }

    /**
     * Check if this version applies to a specific goods/PSV type
     *
     * @param string $type
     * @return bool
     */
    public function appliesToType($type)
    {
        if ($this->goodsOrPsv === null) {
            return true;
        }

        return $this->goodsOrPsv->getId() === $type;
    }
}
