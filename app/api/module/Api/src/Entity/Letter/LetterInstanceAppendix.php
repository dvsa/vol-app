<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterInstanceAppendix Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_instance_appendix")
 */
class LetterInstanceAppendix extends AbstractLetterInstanceAppendix
{
    /**
     * Get effective content - edited content if available, otherwise default from version
     *
     * @return array
     */
    public function getEffectiveContent(): array
    {
        if (!empty($this->editedContent)) {
            if (is_string($this->editedContent)) {
                $decoded = json_decode($this->editedContent, true);
                return is_array($decoded) ? $decoded : [];
            }
            return $this->editedContent;
        }

        $version = $this->getLetterAppendixVersion();
        if ($version !== null) {
            return $version->getDefaultContentAsArray();
        }

        return [];
    }

    /**
     * Check if the content has been edited by a caseworker
     *
     * @return bool
     */
    public function hasBeenEdited(): bool
    {
        return !empty($this->editedContent);
    }

    /**
     * Set edited content from array
     *
     * @param array $content
     * @return self
     */
    public function setEditedContentFromArray(array $content): self
    {
        $this->editedContent = $content;
        return $this;
    }

    /**
     * Check if this is an editable appendix (delegates to version)
     *
     * @return bool
     */
    public function isEditable(): bool
    {
        $version = $this->getLetterAppendixVersion();
        return $version !== null && $version->isEditable();
    }

    /**
     * Check if this is a PDF appendix (delegates to version)
     *
     * @return bool
     */
    public function isPdf(): bool
    {
        $version = $this->getLetterAppendixVersion();
        return $version !== null && $version->isPdf();
    }

    /**
     * Get appendix name (delegates to version)
     *
     * @return string
     */
    public function getName(): string
    {
        $version = $this->getLetterAppendixVersion();
        return $version !== null ? $version->getName() : '';
    }
}
