<?php

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterInstanceSection Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_instance_section",
 *    indexes={
 *        @ORM\Index(name="ix_letter_instance_section_letter_instance_id", columns={"letter_instance_id"}),
 *        @ORM\Index(name="ix_letter_instance_section_letter_section_version_id", columns={"letter_section_version_id"}),
 *        @ORM\Index(name="ix_letter_instance_section_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_instance_section_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class LetterInstanceSection extends AbstractLetterInstanceSection
{
    /**
     * Get the effective content (edited or default)
     *
     * @return array
     */
    public function getEffectiveContent()
    {
        if (!empty($this->editedContent)) {
            return $this->editedContent;
        }
        
        return $this->letterSectionVersion->getDefaultContent() ?: [];
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