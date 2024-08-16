<?php

/**
 * Update translation key text record
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Api\Domain\Command\TranslationKeyText;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

final class Update extends AbstractCommand
{
    use Identity;

    /** @var string */
    protected $translatedText;

    /**
     * @return string
     */
    public function getTranslatedText()
    {
        return $this->translatedText;
    }
}
