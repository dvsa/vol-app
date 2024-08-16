<?php

/**
 * Create translation key text record
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Api\Domain\Command\TranslationKeyText;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

final class Create extends AbstractCommand
{
    /** @var string */
    protected $translationKey;

    /** @var int */
    protected $language;

    /** @var string */
    protected $translatedText;

    /**
     * @return int
     */
    public function getTranslationKey()
    {
        return $this->translationKey;
    }

    /**
     * @return int
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getTranslatedText()
    {
        return $this->translatedText;
    }
}
