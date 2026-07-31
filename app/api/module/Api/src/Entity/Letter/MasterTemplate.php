<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * MasterTemplate Entity
 *
 * A master template supplies the page "chrome" for generated letters. The four
 * EditorJS JSON slot columns are substituted into template_content at render time
 * (see LetterPreviewService::renderSlot()):
 *
 *  - headerLeftContent  -> {{HEADER_LEFT_CONTENT}}  (typically the logo)
 *  - headerRightContent -> {{HEADER_RIGHT_CONTENT}} (typically the address block)
 *  - signoffContent     -> {{SIGNOFF_CONTENT}}      (typically "Yours, ..." + caseworker name)
 *  - footerContent      -> {{FOOTER_CONTENT}}       (typically a single-line footer note)
 *
 * locale is a locale / chrome variant key with an extended vocabulary beyond strict
 * ISO codes — en_GB, en_NI, cy_GB, customN_GB, customN_NI (see the LOCALE_* constants).
 * MasterTemplateResolver picks the template at letter-generation time from the letter
 * context (currently isNi).
 */
#[ORM\Table(name: 'master_template')]
#[ORM\Entity]
class MasterTemplate extends AbstractMasterTemplate
{
    public const LOCALE_EN_GB = 'en_GB';
    public const LOCALE_EN_NI = 'en_NI';
    public const LOCALE_CY_GB = 'cy_GB';

    /**
     * Check if this is the default template for a locale
     *
     * @return bool
     */
    public function isDefaultForLocale()
    {
        return $this->isDefault;
    }

    /**
     * Set this template as the default for its locale
     *
     * @return self
     */
    public function makeDefault()
    {
        $this->isDefault = true;
        return $this;
    }

    /**
     * Remove default status
     *
     * @return self
     */
    public function removeDefault()
    {
        $this->isDefault = false;
        return $this;
    }
}
