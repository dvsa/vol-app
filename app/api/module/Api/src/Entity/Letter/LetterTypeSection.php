<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterTypeSection Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_type_section")
 */
class LetterTypeSection extends AbstractLetterTypeSection
{
    /**
     * Get the effective content (override or default)
     *
     * @return array
     */
    public function getEffectiveContent()
    {
        if (!empty($this->overrideContent)) {
            return $this->overrideContent;
        }

        return $this->letterSectionVersion->getDefaultContent();
    }

    /**
     * Check if this section has override content
     *
     * @return bool
     */
    public function hasOverrideContent()
    {
        return !empty($this->overrideContent);
    }

    /**
     * Clear override content
     *
     * @return self
     */
    public function clearOverrideContent()
    {
        $this->overrideContent = null;
        return $this;
    }

    /**
     * Set override content from array
     *
     * @param array $content
     * @return self
     */
    public function setOverrideContentFromArray(array $content)
    {
        $this->overrideContent = $content;
        return $this;
    }
}
