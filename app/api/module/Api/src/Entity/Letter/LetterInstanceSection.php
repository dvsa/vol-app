<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Service\Letter\EditorJsText;

/**
 * LetterInstanceSection Entity
 */
#[ORM\Table(name: 'letter_instance_section')]
#[ORM\Entity]
class LetterInstanceSection extends AbstractLetterInstanceSection
{
    /**
     * Get the effective content (edited or default)
     *
     * Handles both properly stored arrays and double-encoded JSON strings
     *
     * @return array
     */
    public function getEffectiveContent()
    {
        if (!empty($this->editedContent)) {
            $content = $this->editedContent;

            // If content is a string, it was double-encoded - decode it
            if (is_string($content)) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
                return [];
            }

            return $content;
        }

        if (!empty($this->generatedContent) && is_array($this->generatedContent)) {
            return $this->generatedContent;
        }

        return $this->letterSectionVersion->getDefaultContentAsArray() ?: [];
    }

    /**
     * Check if content has been edited
     *
     * @return bool
     */
    public function hasBeenEdited()
    {
        return !empty($this->editedContent);
    }

    /**
     * Clear edited content
     *
     * @return self
     */
    public function clearEditedContent()
    {
        $this->editedContent = null;
        return $this;
    }

    /**
     * Set edited content from array
     *
     * @param array $content
     * @return self
     */
    public function setEditedContentFromArray(array $content)
    {
        $this->editedContent = $content;
        return $this;
    }

    /**
     * Check if this section requires input
     *
     * @return bool
     */
    public function requiresInput()
    {
        return $this->letterSectionVersion->getRequiresInput();
    }

    /**
     * Whether there's nothing here for a caseworker to edit. That's a section whose wording
     * depends on the letter (the interim paragraph is only there when Interim is ticked), where
     * none of it applied and the letter got the section's empty catch-all instead.
     *
     * A section with just the one wording, left empty, is different: it's there to be written
     * in, so it stays editable. So does a section that asks for input, and one the caseworker
     * has already saved, even if they cleared it, so they can get back to what they wrote.
     *
     * This goes by the section as it's set up now, not as it was when the letter was generated:
     * that isn't recorded. Adding or removing conditional wording later can change the answer
     * for a draft whose section is still empty and untouched.
     */
    public function hasNothingToEdit(): bool
    {
        if ($this->requiresInput() || $this->hasBeenEdited()) {
            return false;
        }

        // The wording first: it's already in hand, and most sections have some. Only an empty
        // one needs its section's other wording looked up.
        if (EditorJsText::plain($this->getEffectiveContent()) !== '') {
            return false;
        }

        $wording = $this->letterSectionVersion->getLetterSectionVariant();

        return $wording !== null
            && $wording->isDefault()
            && (bool) $wording->getLetterSection()?->hasConditionalWording();
    }

    /**
     * Check if content is valid
     *
     * @return bool
     */
    public function isContentValid()
    {
        if ($this->requiresInput() && !$this->hasBeenEdited()) {
            return false;
        }

        $content = $this->getEffectiveContent();
        $contentString = json_encode($content);

        return $this->letterSectionVersion->isContentValid($contentString);
    }
}
